<?php
/**
 * Action: suggestion.box.list
 * Página principal — lista sugestões com busca, filtro por tag e votação.
 */

namespace Modules\SuggestionBox\Actions;

use CController;
use CControllerResponseData;

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
        // Qualquer usuário logado
        return $this->getUserId() > 0;
    }

    protected function doAction(): void {
        $userid  = (int) $this->getUserId();
        $search  = trim($this->getInput('search', ''));
        $tag     = trim($this->getInput('tag', ''));
        $sort    = $this->getInput('sort', 'votes');

        // Buscar tipo do usuário atual
        $currentUser = \DBfetch(\DBselect(
            'SELECT u.userid, u.name, u.surname, u.alias, u.type' .
            ' FROM users u' .
            ' WHERE u.userid=' . $userid
        ));
        $isSuperAdmin = $currentUser && (int)$currentUser['type'] === 3;

        // Construir query de sugestões com contagem de votos
        $where = ['1=1'];

        if ($search !== '') {
            $safe = \DBquote('%' . $search . '%', true);
            // remove as aspas externas que DBquote adiciona para usar em LIKE manual
            $safeLike = "LOWER(s.title) LIKE LOWER(" . \DBquote('%' . $search . '%') . ")
                      OR LOWER(s.description) LIKE LOWER(" . \DBquote('%' . $search . '%') . ")";
            $where[] = '(' . $safeLike . ')';
        }

        if ($tag !== '') {
            $where[] = 'EXISTS (
                SELECT 1 FROM zbx_suggestion_tags st
                WHERE st.suggestionid = s.suggestionid
                AND LOWER(st.tag) = LOWER(' . \DBquote($tag) . ')
            )';
        }

        $orderBy = $sort === 'newest' ? 's.created_at DESC' : 'vote_count DESC, s.created_at DESC';

        $sql =
            'SELECT s.suggestionid, s.userid, s.title, s.description, s.created_at,' .
            '       u.name, u.surname, u.alias,' .
            '       COUNT(DISTINCT v.voteid) AS vote_count' .
            ' FROM zbx_suggestions s' .
            ' JOIN users u ON u.userid = s.userid' .
            ' LEFT JOIN zbx_suggestion_votes v ON v.suggestionid = s.suggestionid' .
            ' WHERE ' . implode(' AND ', $where) .
            ' GROUP BY s.suggestionid, s.userid, s.title, s.description, s.created_at,' .
            '          u.name, u.surname, u.alias' .
            ' ORDER BY ' . $orderBy;

        $res = \DBselect($sql);
        $suggestions = [];
        while ($row = \DBfetch($res)) {
            $suggestions[] = $row;
        }

        // Para cada sugestão: buscar tags e verificar se o usuário atual já votou
        foreach ($suggestions as &$sug) {
            $sid = (int)$sug['suggestionid'];

            // Tags
            $tagRes = \DBselect(
                'SELECT tag FROM zbx_suggestion_tags WHERE suggestionid=' . $sid . ' ORDER BY tag'
            );
            $sug['tags'] = [];
            while ($t = \DBfetch($tagRes)) {
                $sug['tags'][] = $t['tag'];
            }

            // Usuário já votou?
            $voted = \DBfetch(\DBselect(
                'SELECT voteid FROM zbx_suggestion_votes' .
                ' WHERE suggestionid=' . $sid . ' AND userid=' . $userid
            ));
            $sug['user_voted'] = (bool)$voted;
        }
        unset($sug);

        // Todas as tags existentes para o filtro rápido
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
