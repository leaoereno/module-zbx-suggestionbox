<?php
/**
 * Action: suggestion.box.delete
 * Remove uma sugestão. Apenas o autor ou Super Admin pode excluir.
 */

namespace Modules\SuggestionBox\Actions;

use CController;
use CControllerResponseData;

class SuggestionBoxDelete extends CController {

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

        // Buscar sugestão
        $sug = \DBfetch(\DBselect(
            'SELECT suggestionid, userid FROM zbx_suggestions WHERE suggestionid=' . $suggestionid
        ));
        if (!$sug) {
            $this->setResponse(new CControllerResponseData([
                'success' => false,
                'error'   => 'Sugestão não encontrada.',
            ]));
            return;
        }

        // Verificar permissão: autor ou Super Admin
        $currentUser = \DBfetch(\DBselect(
            'SELECT type FROM users WHERE userid=' . $userid
        ));
        $isSuperAdmin = $currentUser && (int)$currentUser['type'] === 3;
        $isAuthor     = (int)$sug['userid'] === $userid;

        if (!$isAuthor && !$isSuperAdmin) {
            $this->setResponse(new CControllerResponseData([
                'success' => false,
                'error'   => 'Sem permissão para excluir esta sugestão.',
            ]));
            return;
        }

        \DBstart();
        $ok = true;

        // Excluir votos e tags primeiro (FK manual, sem CASCADE no schema)
        $ok = $ok && \DBexecute(
            'DELETE FROM zbx_suggestion_votes WHERE suggestionid=' . $suggestionid
        );
        $ok = $ok && \DBexecute(
            'DELETE FROM zbx_suggestion_tags WHERE suggestionid=' . $suggestionid
        );
        $ok = $ok && \DBexecute(
            'DELETE FROM zbx_suggestions WHERE suggestionid=' . $suggestionid
        );

        \DBend($ok);

        $this->setResponse(new CControllerResponseData([
            'success' => $ok,
            'error'   => $ok ? null : 'Erro ao excluir sugestão.',
        ]));
    }
}
