<?php
/**
 * Action: suggestion.box.save
 * Salva nova sugestão (INSERT). Retorna JSON.
 */

namespace Modules\SuggestionBox\Actions;

use CController;
use CControllerResponseData;

class SuggestionBoxSave extends CController {

    protected function init(): void {
        $this->disableCsrfValidation();
    }

    protected function checkInput(): bool {
        $fields = [
            'title'       => 'required|string',
            'description' => 'string',
            'tags'        => 'string',
        ];
        return $this->validateInput($fields);
    }

    protected function checkPermissions(): bool {
        return $this->getUserId() > 0;
    }

    protected function doAction(): void {
        $userid      = (int) $this->getUserId();
        $title       = trim($this->getInput('title', ''));
        $description = trim($this->getInput('description', ''));
        $tagsRaw     = trim($this->getInput('tags', ''));

        if ($title === '') {
            $this->setResponse(new CControllerResponseData([
                'success' => false,
                'error'   => 'O título é obrigatório.',
            ]));
            return;
        }

        \DBstart();
        $ok = true;

        // Gerar ID com MAX+1 (tabela customizada)
        $row = \DBfetch(\DBselect('SELECT MAX(suggestionid) AS maxid FROM zbx_suggestions'));
        $newId = ($row && $row['maxid'] !== null) ? (int)$row['maxid'] + 1 : 1;

        $ok = $ok && \DBexecute(
            'INSERT INTO zbx_suggestions (suggestionid, userid, title, description, created_at)' .
            ' VALUES (' .
                $newId . ',' .
                $userid . ',' .
                \DBquote($title) . ',' .
                \DBquote($description) . ',' .
                \DBquote(date('Y-m-d H:i:s')) .
            ')'
        );

        // Processar tags
        if ($ok && $tagsRaw !== '') {
            $tags = array_unique(array_filter(array_map('trim', explode(',', $tagsRaw))));
            foreach ($tags as $tag) {
                $tag = mb_strtolower(mb_substr($tag, 0, 50));
                if ($tag === '') continue;

                $tRow = \DBfetch(\DBselect('SELECT MAX(tagid) AS maxid FROM zbx_suggestion_tags'));
                $tagId = ($tRow && $tRow['maxid'] !== null) ? (int)$tRow['maxid'] + 1 : 1;

                $ok = $ok && \DBexecute(
                    'INSERT INTO zbx_suggestion_tags (tagid, suggestionid, tag)' .
                    ' VALUES (' . $tagId . ',' . $newId . ',' . \DBquote($tag) . ')'
                );
            }
        }

        \DBend($ok);

        $this->setResponse(new CControllerResponseData([
            'success'      => $ok,
            'suggestionid' => $ok ? $newId : null,
            'error'        => $ok ? null : 'Erro ao salvar sugestão.',
        ]));
    }
}
