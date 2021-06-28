<?php

namespace App\Presenters;

use Doctrine\ORM\EntityManagerInterface;
use HTMLPurifier;
use HTMLPurifier_Config;
use Nette;
use Tracy\ILogger;
use App;
use App\Model\Entity\User;
use App\Model\Repository\Users;
use App\Users\UserManager;
use App\Helpers\Date\DateHelper;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /**
     * @var ILogger
     * @inject
     */
    public $logger;
    /**
     * @var UserManager
     * @inject
     */
    public $userManager;
    /**
     * @var App\Users\RolesManager
     * @inject
     */
    public $rolesManager;
    /**
     * @var Nette\Http\IRequest
     * @inject
     */
    public $httpRequest;
    /**
     * @var App\Users\MyAuthorizator
     * @inject
     */
    public $myAuthorizator;
    /**
     * @var App\Helpers\ConfigParams
     * @inject
     */
    public $configParams;

    /**
     * @var EntityManagerInterface
     * @inject
     */
    public $em;
    /**
     * @var Users
     * @inject
     */
    public $users;

    /**
     * Currently logged user.
     * @var User
     */
    protected $currentUser;
    /**
     * @var DateHelper
     * @inject
     */
    public $dateHelper;


    protected function createComponentMenu()
    {
        return new App\Menu\MenuControl();
    }

    protected function isLoggedIn()
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect('Homepage:login');
        }
        return true;
    }

    protected function addLatteFilters()
    {
        $this->template->addFilter('stripUnicode', new App\Latte\Filters\StripUnicodeFilter);
    }

    protected function addDoctrineFilters()
    {
        $this->em->getConfiguration()->addFilter("softdeletable", "\App\Filters\SoftdeletableFilter");
        $this->enableDoctrineFilters();
    }

    protected function enableDoctrineFilters()
    {
        $this->em->getFilters()->enable("softdeletable");
    }

    protected function disableDoctrineFilters()
    {
        $this->em->getFilters()->disable("softdeletable");
    }

    protected function startup()
    {
        parent::startup();

        // get current user and forward variables to template
        $this->currentUser = $this->userManager->getCurrentUser();
        $this->template->currentUser = $this->currentUser;
        $this->template->myAuthorizator = $this->myAuthorizator;

        // engage filters
        $this->addLatteFilters();
        $this->addDoctrineFilters();
    }

    public function afterRender()
    {
        parent::afterRender();

        if ($this->isAjax() && $this->hasFlashSession()) {
            $this->redrawControl('flashes');
        }
    }

    protected function setDefaultLayout()
    {
        // we are in exchange system and under login, so set appropriate layout
        $this->setLayout('layout');
    }

    public function sanitizeTinyMCEOutput($in)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.SafeIframe', true);
        $config->set(
            'URI.SafeIframeRegexp',
            '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'
        ); //allow YouTube and Vimeo
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($in);
    }

    public function generateRandomColor()
    {
        $colors[] = 'red';
        $colors[] = 'pink';
        $colors[] = 'purple';
        $colors[] = 'deep-purple';
        $colors[] = 'indigo';
        $colors[] = 'blue';
        $colors[] = 'light-blue';
        $colors[] = 'cyan';
        $colors[] = 'teal';
        $colors[] = 'green';
        $colors[] = 'light-green';
        $colors[] = 'lime';
        $colors[] = 'yellow';
        $colors[] = 'amber';
        $colors[] = 'orange';
        $colors[] = 'deep-orange';
        $colors[] = 'brown';
        $colors[] = 'grey';
        $colors[] = 'blue-grey';

        return $colors[mt_rand(0, count($colors) - 1)];
    }

    private function setPaginator($page, $count, $limit)
    {
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($count);
        $paginator->setItemsPerPage($limit);
        $paginator->setPage($page);
        $this->template->paginator = $paginator;
    }

    protected function paginate($query, $page = 1, $limit = null)
    {
        if (!$limit) {
            $limit = $this->configParams->itemsPerPage;
        }

        if ($page < 1) {
            $page = 1;
        }

        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // offset
            ->setMaxResults($limit); // limit

        $this->setPaginator($page, $paginator->count(), $limit);
        return $paginator;
    }
}
