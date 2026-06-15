<?php
namespace Modules\SuggestionBox;

use Zabbix\Core\CModule;
use APP;
use CMenuItem;
use CWebUser;

class Module extends CModule {

    public function init(): void {
        // Menu Serviços → Suggestion Box para todos os usuários
        APP::Component()->get('menu.main')
            ->findOrAdd(_('Services'))
            ->getSubmenu()
            ->add((new CMenuItem(_('Suggestion Box')))
                ->setAction('suggestion.box.list')
            );

        // Menu Administração → Suggestions Report apenas para Super Admin (role.type = 3)
        $userid = (int) CWebUser::$data['userid'];
        if ($userid > 0) {
            $user = \DBfetch(\DBselect(
                'SELECT r.type FROM users u JOIN role r ON r.roleid=u.roleid WHERE u.userid=' . $userid
            ));
            if ($user && (int)$user['type'] === 3) {
                APP::Component()->get('menu.main')
                    ->findOrAdd(_('Administration'))
                    ->getSubmenu()
                    ->add((new CMenuItem(_('Suggestions Report')))
                        ->setAction('suggestion.box.report')
                    );
            }
        }
    }
}
