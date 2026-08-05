<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Services\FaqService;
use RuntimeException;

final class FaqController extends Controller
{
    private FaqService $faqs;

    public function __construct(?FaqService $faqs = null)
    {
        $this->faqs = $faqs ?? new FaqService();
    }

    public function index(): void
    {
        Auth::requirePermission('faqs.view');
        $result = $this->faqs->list($_GET);
        View::renderInLayout('faqs/index', 'admin', [
            'title' => 'FAQs',
            'activeNav' => 'faqs',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'FAQs'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'faqs' => $result['faqs'],
            'paginator' => $result['paginator'],
            'filters' => $result['filters'],
            'notifications' => [],
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('faqs.create');
        $this->renderForm(null, Session::getFlash('old') ?? []);
    }

    public function store(): void
    {
        Auth::requirePermission('faqs.create');
        $this->validateCsrf();
        try {
            $id = $this->faqs->create($_POST);
            flash('success', 'FAQ created successfully.');
            redirect('/faqs/' . $id . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/faqs/create');
        }
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('faqs.update');
        $faq = $this->faqs->find((int) $id, true);
        if ($faq === null) {
            flash('error', 'FAQ not found.');
            redirect('/faqs');
        }
        $old = Session::getFlash('old');
        $this->renderForm($faq, is_array($old) ? $old : $faq);
    }

    public function update(string $id): void
    {
        Auth::requirePermission('faqs.update');
        $this->validateCsrf();
        $faqId = (int) $id;
        try {
            $this->faqs->update($faqId, $_POST);
            flash('success', 'FAQ updated successfully.');
            redirect('/faqs/' . $faqId . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/faqs/' . $faqId . '/edit');
        }
    }

    public function destroy(string $id): void
    {
        Auth::requirePermission('faqs.delete');
        $this->validateCsrf();
        $this->faqs->softDelete((int) $id);
        flash('success', 'FAQ moved to trash.');
        redirect('/faqs');
    }

    private function renderForm(?array $faq, array $values): void
    {
        View::renderInLayout('faqs/form', 'admin', [
            'title' => $faq ? 'Edit FAQ' : 'Add FAQ',
            'activeNav' => 'faqs',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'FAQs', 'href' => url('/faqs')],
                ['label' => $faq ? 'Edit' : 'Add'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'faq' => $faq,
            'values' => $values,
            'notifications' => [],
        ]);
    }
}
