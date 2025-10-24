<?php declare(strict_types=1);

namespace UserPlay\Presentation\Main;

use UserPlay\Presentation\BasePresenter;


final class MainPresenter extends BasePresenter
{
    public function actionDefault(): void
    {
        // Render the main page with the form
        $this->setView('main');

        $template         = $this->getTemplate();
        $template->apiUrl = $this->link('User:process');
    }

    public function renderDefault(): void
    {
        $this->sendTemplate();
    }
}
