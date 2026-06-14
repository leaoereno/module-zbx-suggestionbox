<?php
/**
 * Action: suggestion.box.vote
 * Toggle de voto: vota ou remove o voto do usuário atual em uma sugestão.
 */

namespace Modules\SuggestionBox\Actions;

use CController;
use CControllerResponseData;

class SuggestionBoxVote extends CController {

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        $fields = [
            'suggestionid' => 'required|db zbx_suggestions.suggestionid',
        ];
        return $this->validateInput($fields);
    }

    protected function checkPermissions(): bool {
        return $this->getUserId() > 0;
    }

    protected function doAction(): void {
        $userid       = (int) $this->getUserId();
        $suggestionid = (int) $this->getInput('suggestionid');

        // Verificar se sugestão existe
        $sug = \DBfetch(\DBselect(
            'SELECT suggestionid FROM zbx_suggestions WHERE suggestionid=' . $suggestionid
        ));
        if (!$sug) {
            $this->setResponse(new CControllerResponseData([
                'success' => false,
                'error'   => 'Sugestão não encontrada.',
            ]));
            return;
        }

        // Verificar voto atual
        $existing = \DBfetch(\DBselect(
            'SELECT voteid FROM zbx_suggestion_votes' .
            ' WHERE suggestionid=' . $suggestionid . ' AND userid=' . $userid
        ));

        \DBstart();
        $ok = true;

        if ($existing) {
            // Remove voto
            $ok = \DBexecute(
                'DELETE FROM zbx_suggestion_votes WHERE voteid=' . (int)$existing['voteid']
            );
            $voted = false;
        } else {
            // Adiciona voto
            $row = \DBfetch(\DBselect('SELECT MAX(voteid) AS maxid FROM zbx_suggestion_votes'));
            $newId = ($row && $row['maxid'] !== null) ? (int)$row['maxid'] + 1 : 1;

            $ok = \DBexecute(
                'INSERT INTO zbx_suggestion_votes (voteid, suggestionid, userid, voted_at)' .
                ' VALUES (' . $newId . ',' . $suggestionid . ',' . $userid . ',' .
                \DBquote(date('Y-m-d H:i:s')) . ')'
            );
            $voted = true;
        }

        \DBend($ok);

        // Contagem atualizada
        $countRow = \DBfetch(\DBselect(
            'SELECT COUNT(*) AS cnt FROM zbx_suggestion_votes WHERE suggestionid=' . $suggestionid
        ));
        $voteCount = (int)($countRow['cnt'] ?? 0);

        $this->setResponse(new CControllerResponseData([
            'success'   => $ok,
            'voted'     => $voted,
            'voteCount' => $voteCount,
            'error'     => $ok ? null : 'Erro ao registrar voto.',
        ]));
    }
}
