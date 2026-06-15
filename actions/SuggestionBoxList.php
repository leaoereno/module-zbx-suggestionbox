<?php
namespace Modules\SuggestionBox\Actions;

use CController,
    CControllerResponseData,
    CWebUser;

class SuggestionBoxList extends CController {

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        $fields = [
            'search' => 'string',
            'tag'    => 'string',
            'sort'   => 'in votes,newest',
        ];
        return $this->validateInput($fields);
    }

    protected function checkPermissions(): bool {
        return (int) CWebUser::$data['userid'] > 0;
    }

    protected function doAction(): void {
        $userid  = (int) CWebUser::$data['userid'];
        $search  = trim($this->getInput('search', ''));
        $tag     = trim($this->getInput('tag', ''));
        $sort    = $this->getInput('sort', 'votes');

        $currentUser = \DBfetch(\DBselect(
            'SELECT u.userid, u.name, u.surname, u.username, r.type' .
            ' FROM users u JOIN role r ON r.roleid=u.roleid WHERE u.userid=' . $userid
        ));
        $isSuperAdmin = $currentUser && (int)$currentUser['type'] === 3;

        $where = ['1=1'];

        if ($search !== '') {
            $where[] = '(LOWER(s.title) LIKE LOWER(' . zbx_dbstr('%' . $search . '%') . ')' .
                       ' OR LOWER(s.description) LIKE LOWER(' . zbx_dbstr('%' . $search . '%') . '))';
        }

        if ($tag !== '') {
            $where[] = 'EXISTS (SELECT 1 FROM zbx_suggestion_tags st' .
                       ' WHERE st.suggestionid = s.suggestionid' .
                       ' AND LOWER(st.tag) = LOWER(' . zbx_dbstr($tag) . '))';
        }

        $orderBy = $sort === 'newest' ? 's.created_at DESC' : 'vote_count DESC, s.created_at DESC';

        $sql =
            'SELECT s.suggestionid, s.userid, s.title, s.description, s.created_at,' .
            '       u.name, u.surname, u.username,' .
            '       COUNT(DISTINCT v.voteid) AS vote_count' .
            ' FROM zbx_suggestions s' .
            ' JOIN users u ON u.userid = s.userid' .
            ' LEFT JOIN zbx_suggestion_votes v ON v.suggestionid = s.suggestionid' .
            ' WHERE ' . implode(' AND ', $where) .
            ' GROUP BY s.suggestionid, s.userid, s.title, s.description, s.created_at,' .
            '          u.name, u.surname, u.username' .
            ' ORDER BY ' . $orderBy;

        $res = \DBselect($sql);
        $suggestions = [];
        while ($row = \DBfetch($res)) {
            $suggestions[] = $row;
        }

        foreach ($suggestions as &$sug) {
            $sid = (int)$sug['suggestionid'];

            $tagRes = \DBselect(
                'SELECT tag FROM zbx_suggestion_tags WHERE suggestionid=' . $sid . ' ORDER BY tag'
            );
            $sug['tags'] = [];
            while ($t = \DBfetch($tagRes)) {
                $sug['tags'][] = $t['tag'];
            }

            $voted = \DBfetch(\DBselect(
                'SELECT voteid FROM zbx_suggestion_votes' .
                ' WHERE suggestionid=' . $sid . ' AND userid=' . $userid
            ));
            $sug['user_voted'] = (bool)$voted;
        }
        unset($sug);

        $tagListRes = \DBselect(
            'SELECT DISTINCT tag FROM zbx_suggestion_tags ORDER BY tag'
        );
        $allTags = [];
        while ($t = \DBfetch($tagListRes)) {
            $allTags[] = $t['tag'];
        }

        $this->setResponse(new CControllerResponseData([
            'suggestions'  => $suggestions,
            'allTags'      => $allTags,
            'search'       => $search,
            'activeTag'    => $tag,
            'sort'         => $sort,
            'currentUser'  => $currentUser,
            'isSuperAdmin' => $isSuperAdmin,
        ]));
    }
}
