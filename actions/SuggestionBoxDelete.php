<?php
namespace Modules\SuggestionBox\Actions;

use CController,
    CControllerResponseData,
    CWebUser;

class SuggestionBoxDelete extends CController {

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
            'SELECT suggestionid, userid FROM zbx_suggestions WHERE suggestionid=' . $suggestionid
        ));
        if (!$sug) {
            $this->setResponse(new CControllerResponseData([
                'main_block' => json_encode(['success' => false, 'error' => 'Sugestão não encontrada.'])
            ]));
            return;
        }

        $currentUser = \DBfetch(\DBselect(
            'SELECT r.type FROM users u JOIN role r ON r.roleid=u.roleid WHERE u.userid=' . $userid
        ));
        $isSuperAdmin = $currentUser && (int)$currentUser['type'] === 3;
        $isAuthor     = (int)$sug['userid'] === $userid;

        if (!$isAuthor && !$isSuperAdmin) {
            $this->setResponse(new CControllerResponseData([
                'main_block' => json_encode(['success' => false, 'error' => 'Sem permissão.'])
            ]));
            return;
        }

        \DBstart();
        $ok = \DBexecute('DELETE FROM zbx_suggestion_votes WHERE suggestionid=' . $suggestionid);
        $ok = $ok && \DBexecute('DELETE FROM zbx_suggestion_tags WHERE suggestionid=' . $suggestionid);
        $ok = $ok && \DBexecute('DELETE FROM zbx_suggestions WHERE suggestionid=' . $suggestionid);
        \DBend($ok);

        $this->setResponse(new CControllerResponseData([
            'main_block' => json_encode([
                'success' => $ok,
                'error'   => $ok ? null : 'Erro ao excluir.',
            ])
        ]));
    }
}
