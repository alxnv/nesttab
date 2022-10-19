<?php
// controller that remember 'prev' parameter
// NOT USED NOW
namespace Alxnv\Nesttab\Http\Controllers;

class StructPrevController extends BasicController
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function __construct()
    {
        parent::__construct();
        $user = \yy::testlogged();
        $this->prev_link = (isset($r['prev']) ? substr($r['prev'], 0, 500) : '');
        if (!($user['can_modify_structure'] && $user['all_tables'])) {
            \yy::gotoErrorPage(\yy::t('Access denied'));
        }
    }
}