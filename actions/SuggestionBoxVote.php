<?php
namespace Modules\SuggestionBox\Actions;

use CController,
    CControllerResponseData,
    CWebUser;

class SuggestionBoxVote extends CController {

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        $fields = ['suggestionid' => 'required|int32'];
        return $this->validateInput($fields);
    }

    protected function checkPermissions(): bool {
        return (int) CWebUser::$data['userid'] > 0;
    }

    protected function doAction(): void {
        $userid       = (int) CWebUser::$data['userid'];
        $suggestionid = (int) $this->getInput('suggestionid');

        $sug = \DBfetch(\DBselect(
            'SELECT suggestionid FROM zbx_suggestions WHERE suggestionid=' . $suggestionid
        ));
        if (!$sug) {
            $this->setResponse(new CControllerResponseData([
                'main_block' => json_encode(['success' => false, 'error' => 'Sugestão não encontrada.'])
            ]));
            return;
        }

        $existing = \DBfetch(\DBselect(
            'SELECT voteid FROM zbx_suggestion_votes' .
            ' WHERE suggestionid=' . $suggestionid . ' AND userid=' . $userid
        ));

        \DBstart();

        if ($existing) {
            $ok = \DBexecute('DELETE FROM zbx_suggestion_votes WHERE voteid=' . (int)$existing['voteid']);
            $voted = false;
        } else {
            $row = \DBfetch(\DBselect('SELECT MAX(voteid) AS maxid FROM zbx_suggestion_votes'));
            $newId = ($row && $row['maxid'] !== null) ? (int)$row['maxid'] + 1 : 1;
            $ok = \DBexecute(
                'INSERT INTO zbx_suggestion_votes (voteid, suggestionid, userid, voted_at)' .
                ' VALUES (' . $newId . ',' . $suggestionid . ',' . $userid . ',' .
                zbx_dbstr(date('Y-m-d H:i:s')) . ')'
            );
            $voted = true;
        }

        \DBend($ok);

        $countRow = \DBfetch(\DBselect(
            'SELECT COUNT(*) AS cnt FROM zbx_suggestion_votes WHERE suggestionid=' . $suggestionid
        ));
        $voteCount = (int)($countRow['cnt'] ?? 0);

        $this->setResponse(new CControllerResponseData([
            'main_block' => json_encode([
                'success'   => $ok,
                'voted'     => $voted,
                'voteCount' => $voteCount,
                'error'     => $ok ? null : 'Erro ao registrar voto.',
            ])
        ]));
    }
}
