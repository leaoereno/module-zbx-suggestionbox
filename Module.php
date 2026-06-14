<?php
/**
 * Suggestion Box — Zabbix 7.0 Module
 * Permite que usuários criem sugestões/ideias e votem nas dos outros.
 *
 * @author  Rafael Leão — NOC Claro Empresas / Embratel
 * @version 1.0.0
 */

namespace Modules\SuggestionBox;

use Zabbix\Core\CModule;
use APP;
use CMenuItem;

class Module extends CModule {

    public function init(): void {
        // Menu item sob "Serviços" para todos os usuários
        APP::Component()->get('menu.main')
            ->findOrAdd(_('Services'))
            ->getSubmenu()
            ->add((new CMenuItem(_('Suggestion Box')))
                ->setAction('suggestion.box.list')
            );

        // Menu item sob "Administração" apenas para Super Admin (verificado na action)
        APP::Component()->get('menu.main')
            ->findOrAdd(_('Administration'))
            ->getSubmenu()
            ->add((new CMenuItem(_('Suggestions Report')))
                ->setAction('suggestion.box.report')
            );
    }
}
