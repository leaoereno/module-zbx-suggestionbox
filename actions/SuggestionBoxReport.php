<?php
/**
 * Action: suggestion.box.report
 * Relatório de sugestões mais votadas — apenas Super Admin.
 */

namespace Modules\SuggestionBox\Actions;

use CController;
use CControllerResponseData,
    CWebUser;

class SuggestionBoxReport extends CController {

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        $fields = [
            'limit' => 'int32',
        ];
        return $this->validateInput($fields);
    }

    protected function checkPermissions(): bool {
        $user = \DBfetch(\DBselect(
            'SELECT r.type FROM users u JOIN role r ON r.roleid=u.roleid WHERE u.userid=' . (int)(int) CWebUser::$data['userid']
        ));
        return $user && (int)$user['type'] === 3;
    }

    protected function doAction(): void {
        $limit = max(5, min(100, (int)$this->getInput('limit', 20)));

        $sql =
            'SELECT s.suggestionid, s.title, s.description, s.created_at,' .
            '       u.name, u.surname, u.username,' .
            '       COUNT(DISTINCT v.voteid) AS vote_count' .
            ' FROM zbx_suggestions s' .
            ' JOIN users u ON u.userid = s.userid' .
            ' LEFT JOIN zbx_suggestion_votes v ON v.suggestionid = s.suggestionid' .
            ' GROUP BY s.suggestionid, s.title, s.description, s.created_at,' .
            '          u.name, u.surname, u.username' .
            ' ORDER BY vote_count DESC, s.created_at DESC';

        $res = \DBselect($sql, $limit);
        $suggestions = [];
        while ($row = \DBfetch($res)) {
            $suggestions[] = $row;
        }

        // Tags por sugestão
        foreach ($suggestions as &$sug) {
            $tagRes = \DBselect(
                'SELECT tag FROM zbx_suggestion_tags WHERE suggestionid=' .
                (int)$sug['suggestionid'] . ' ORDER BY tag'
            );
            $sug['tags'] = [];
            while ($t = \DBfetch($tagRes)) {
                $sug['tags'][] = $t['tag'];
            }
        }
        unset($sug);

        // Totais para o header do relatório
        $totals = \DBfetch(\DBselect(
            'SELECT COUNT(*) AS total_suggestions,' .
            ' (SELECT COUNT(*) FROM zbx_suggestion_votes) AS total_votes' .
            ' FROM zbx_suggestions'
        ));

        $this->setResponse(new CControllerResponseData([
            'suggestions'       => $suggestions,
            'limit'             => $limit,
            'total_suggestions' => (int)($totals['total_suggestions'] ?? 0),
            'total_votes'       => (int)($totals['total_votes'] ?? 0),
        ]));
    }
}
