<?php

namespace App\Forms;

use Nette;
use App\Model\Entity\User;
use App\Users\UserManager;
use App\Model\Entity\Page;
use App\Model\Repository\Pages;
use App\Model\Repository\Faculties;

/**
 * Class containing factory methods for forms mainly concerning articles.
 * Alongside factories there can also be success callbacks.
 */
class PagesFormsFactory
{
    use Nette\SmartObject;

    /** @var Pages */
    private $pages;
    /** @var Faculties */
    private $faculties;
    /** @var User */
    private $user;

    /**
     * DI Constructor.
     * @param Pages $pages
     * @param UserManager $userManager
     * @param Faculties $faculties
     */
    public function __construct(
        Pages $pages,
        UserManager $userManager,
        Faculties $faculties
    ) {

        $this->pages = $pages;
        $this->faculties = $faculties;
        $this->user = $userManager->getCurrentUser();
    }

    /**
     * Create edit page/article form.
     * @param Page $page
     * @param int $facultyId
     * @return MyForm
     */
    public function createPagesForm($page, $facultyId = null)
    {
        $form = new MyForm;
        $form->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');

        $form->addOriginalTextArea('content', 'Content')
                ->setHtmlAttribute('id', 'content')
                ->setHtmlAttribute('class', 'tinymce')
                ->setDefaultValue($page->getContent());
        $form->addHidden('id', $page->getId());
        $form->addHidden('facultyId', $facultyId);

        $form->addSubmit('send', 'Edit page');
        $form->onSuccess[] = array($this, 'pagesFormSucceeded');
        return $form;
    }

    /**
     * Success callback for the page editation form.
     * @param MyForm $form
     * @param object $values
     */
    public function pagesFormSucceeded(MyForm $form, $values)
    {
        if (strlen($values->content) > 50000) {
            $form->addError('Content is too long.');
            return;
        }

        $page = $this->pages->findOrThrow($values->id);

        if (!$page->getFaculty() && $values->facultyId) {
            // create new article
            $faculty = $this->faculties->findOrThrow($values->facultyId);
            $newPage = new Page($page->getPageName(), $page->getPageSubname(), $page->getTitle(), $values->content, $faculty);
            $newPage->modified($this->user);
            $this->pages->persist($newPage);
        } else {
            // update old one
            $page->setContent($values->content);
            $page->modified($this->user);
            $this->pages->flush();
        }
    }
}
