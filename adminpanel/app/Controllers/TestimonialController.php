<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Services\TestimonialService;
use RuntimeException;

final class TestimonialController extends Controller
{
    private TestimonialService $testimonials;

    public function __construct(?TestimonialService $testimonials = null)
    {
        $this->testimonials = $testimonials ?? new TestimonialService();
    }

    public function index(): void
    {
        Auth::requirePermission('testimonials.view');
        $result = $this->testimonials->list($_GET);
        View::renderInLayout('testimonials/index', 'admin', [
            'title' => 'Testimonials',
            'activeNav' => 'testimonials',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Testimonials'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'testimonials' => $result['testimonials'],
            'paginator' => $result['paginator'],
            'filters' => $result['filters'],
            'notifications' => [],
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('testimonials.create');
        $this->renderForm(null, Session::getFlash('old') ?? []);
    }

    public function store(): void
    {
        Auth::requirePermission('testimonials.create');
        $this->validateCsrf();
        try {
            $id = $this->testimonials->create($_POST, $_FILES['thumbnail'] ?? null);
            flash('success', 'Testimonial created successfully.');
            redirect('/testimonials/' . $id . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/testimonials/create');
        }
    }

    public function edit(string $id): void
    {
        Auth::requirePermission('testimonials.update');
        $testimonial = $this->testimonials->find((int) $id, true);
        if ($testimonial === null) {
            flash('error', 'Testimonial not found.');
            redirect('/testimonials');
        }
        $old = Session::getFlash('old');
        $this->renderForm($testimonial, is_array($old) ? $old : $testimonial);
    }

    public function update(string $id): void
    {
        Auth::requirePermission('testimonials.update');
        $this->validateCsrf();
        $testimonialId = (int) $id;
        try {
            $this->testimonials->update($testimonialId, $_POST, $_FILES['thumbnail'] ?? null);
            flash('success', 'Testimonial updated successfully.');
            redirect('/testimonials/' . $testimonialId . '/edit');
        } catch (RuntimeException $e) {
            Session::flash('old', $_POST);
            flash('error', $e->getMessage());
            redirect('/testimonials/' . $testimonialId . '/edit');
        }
    }

    public function destroy(string $id): void
    {
        Auth::requirePermission('testimonials.delete');
        $this->validateCsrf();
        $this->testimonials->softDelete((int) $id);
        flash('success', 'Testimonial moved to trash.');
        redirect('/testimonials');
    }

    private function renderForm(?array $testimonial, array $values): void
    {
        View::renderInLayout('testimonials/form', 'admin', [
            'title' => $testimonial ? 'Edit Testimonial' : 'Add Testimonial',
            'activeNav' => 'testimonials',
            'breadcrumbs' => [
                ['label' => 'Admin', 'href' => url('/dashboard')],
                ['label' => 'Testimonials', 'href' => url('/testimonials')],
                ['label' => $testimonial ? 'Edit' : 'Add'],
            ],
            'user' => Auth::user(),
            'csrf' => Csrf::field(),
            'testimonial' => $testimonial,
            'values' => $values,
            'notifications' => [],
        ]);
    }
}
