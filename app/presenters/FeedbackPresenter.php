<?php

namespace App\Presenters;

use App;
use App\Model\Repository\Countries;
use App\Model\Repository\FeedbackRepository;
use Nette;

/**
 * Feedback presenter.
 */
class FeedbackPresenter extends BasePresenter
{
    /**
     * @var App\Forms\FeedbackFormsFactory
     * @inject
     */
    public $feedbackFormsFactory;
    /**
     * @var App\Helpers\FeedbackHelper
     * @inject
     */
    public $feedbackHelpers;
    /**
     * @var App\Helpers\Pdf\FeedbackPdfFactory
     * @inject
     */
    public $feedbackPdfHelper;
    /**
     * @var Countries
     * @inject
     */
    public $countries;
    /**
     * @var FeedbackRepository
     * @inject
     */
    public $feedbackRepository;

    protected function createAddFeedbackForm($countryId)
    {
        $form = $this->feedbackFormsFactory->createAddFeedbackForm($countryId);
        return $form;
    }

    protected function createComponentCountriesManagementForm()
    {
        $form = $this->feedbackFormsFactory->createCountriesManagementForm();
        $form->onSuccess[] = function ($form, $values) {
            $this->flashMessage('Countries were successfully modified');
        };
        return $form;
    }

    protected function createFilterFeedbackAjaxForm(
        $exchange_type,
        $start_date,
        $end_date,
        $host_city,
        $host_faculty
    ) {
        $form = $this->feedbackFormsFactory->createFilterFeedbackForm(
            $exchange_type,
            $start_date,
            $end_date,
            $host_city,
            $host_faculty
        );
        $form->elementPrototype->addClass('ajax');
        return $form;
    }

    protected function createFilterFeedbackListAjaxForm(
        $country,
        $start_date,
        $end_date
    ) {
        $form = $this->feedbackFormsFactory->createFilterFeedbackListForm(
            $country,
            $start_date,
            $end_date
        );
        $form->elementPrototype->addClass('ajax');
        return $form;
    }

    protected function startup()
    {
        parent::startup();
        $this->setLayout('layoutPublic');
    }

    public function renderDefault()
    {
        $this->template->countries = $this->countries->getIfmsaCountries();
    }

    public function actionCountry(
        $id,
        $page,
        $orderby,
        $order,
        $exchangeType,
        $startDate,
        $endDate,
        $hostCity,
        $hostFaculty
    ) {
        $country = $this->countries->findOrThrow($id);

        $year = date("Y");
        $startDateTyped = null;
        $endDateTyped = null;
        $start = date_create_from_format("j. n. Y", $startDate);
        $end = date_create_from_format("j. n. Y", $endDate);
        if ($start) {
            $startDateTyped = $start->format("j. n. Y");
        }
        if ($end) {
            $endDateTyped = $end->format("j. n. Y");
        }

        // get data from database and engage pagination
        $query = $this->feedbackRepository->getCountryFeedbackListQuery(
            $orderby,
            $order,
            $country,
            $startDateTyped,
            $endDateTyped,
            $exchangeType,
            $hostCity,
            $hostFaculty
        );
        $paginator = $this->paginate($query, $page);

        // forward data to template
        $this->template->feedback = $paginator->getIterator();
        $this->template->country = $country;

        // if dates are still nulls lets type them
        if ($startDateTyped == null) {
            $startDateTyped = date("1. 1. " . $year);
        }
        if ($endDateTyped == null) {
            $endDateTyped = date("31. 12. " . $year);
        }

        // create form
        $this['filterFeedbackForm'] = $this->createFilterFeedbackAjaxForm(
            $exchangeType,
            $startDateTyped,
            $endDateTyped,
            $hostCity,
            $hostFaculty
        );

        if ($this->isAjax()) {
            $this->redrawControl('feedbackList');
        }
    }

    public function actionFeedbackDetail($id)
    {
        $feedback = $this->feedbackRepository->findOrThrow($id);

        $this->template->feed = $feedback;
        $this->template->country = $feedback->country;
        $this->template->feedbackHelpers = $this->feedbackHelpers;
    }

    public function actionAddFeedback($id)
    {
        $this['addFeedbackForm'] = $this->createAddFeedbackForm($id);
    }

    public function actionCountriesManagement()
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Feedback", "management")) {
            $this->error('Access denied');
        }

        $this->template->countries = $this->countries->findAll();

        // set private layout
        $this->setDefaultLayout();
    }

    public function actionDeleteFeedback($id)
    {
        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Feedback", "management")) {
            $this->error('Access denied');
        }

        $feedback = $this->feedbackRepository->findOrThrow($id);
        $feedback->delete();
        $this->feedbackRepository->flush();

        $this->flashMessage('Feedback was successfully deleted');
        $this->redirect('Feedback:list');
    }

    public function actionFeedbackPdf($id, $fancy)
    {
        $feedback = $this->feedbackRepository->findOrThrow($id);
        $this->feedbackPdfHelper->createFeedbackPdf($feedback, $fancy);
    }

    public function actionList(
        $page,
        $orderby,
        $order,
        $country,
        $startDate,
        $endDate
    ) {

        if (!$this->isLoggedIn() ||
                !$this->user->isAllowed("Feedback", "management")) {
            $this->error('Access denied');
        }

        // parse and get all parameters
        $countryTyped = null;
        if ($country) {
            $countryTyped = $this->countries->get($country);
        }
        $year = date("Y");
        $startDateTyped = null;
        $endDateTyped = null;
        $start = date_create_from_format("j. n. Y", $startDate);
        $end = date_create_from_format("j. n. Y", $endDate);
        if ($start) {
            $startDateTyped = $start->format("j. n. Y");
        }
        if ($end) {
            $endDateTyped = $end->format("j. n. Y");
        }

        // get data from database and engage pagination
        $query = $this->feedbackRepository->getCountryFeedbackListQuery(
            $orderby,
            $order,
            $countryTyped,
            $startDateTyped,
            $endDateTyped
        );
        $paginator = $this->paginate($query, $page);

        // forward data to template
        $this->template->feedbackList = $paginator->getIterator();

        // if dates are still nulls lets type them
        if ($startDateTyped == null) {
            $startDateTyped = date("1. 1. " . $year);
        }
        if ($endDateTyped == null) {
            $endDateTyped = date("31. 12. " . $year);
        }

        // create form
        $this['filterFeedbackListForm'] = $this->createFilterFeedbackListAjaxForm(
            $country,
            $startDateTyped,
            $endDateTyped
        );

        // set private layout
        $this->setDefaultLayout();

        if ($this->isAjax()) {
            $this->redrawControl('feedbackList');
        }
    }
}
