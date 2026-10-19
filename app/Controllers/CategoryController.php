<?php

namespace App\Controllers;

use App\Models\Category;
use App\Services\Validation;

/**
 * Called by api/categories.php. It only works with data and returns arrays:
 * no HTML, no redirects, no flash messages. The API file turns the arrays into JSON,
 * and also checks the role and the CSRF token before any method here runs.
 */
class CategoryController {

    private const PER_PAGE = 10;

    /**
     * The categories of the current page, plus what the page needs to draw the page numbers.
     * $query is $_GET (q, status, page).
     */
    public static function index($query){

        $search = trim($query['q'] ?? '');
        $status = $query['status'] ?? '';
        $page   = max(1, (int) ($query['page'] ?? 1));

        $model = new Category();
        $total = $model->count($search, $status);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min($page, $pages);   // never go past the last page

        $categories = $model->paginate($search, $status, self::PER_PAGE, ($page - 1) * self::PER_PAGE);

        // Add "how many research items use it" to every row for the table.
        foreach ($categories as $key => $category) {
            $categories[$key]['research_count'] = $model->countResearch((int) $category['id']);
        }

        return [
            'categories' => $categories,
            'total'      => $total,
            'page'       => $page,
            'pages'      => $pages,
            'perPage'    => self::PER_PAGE,
        ];
    }

    /**
     * Saves the form. $id is null when adding and the category id when editing.
     * Returns ['success' => bool, 'errors' => [field => [messages]], 'message' => text for the toast]
     */
    public static function save($request, $id = null){

        // When editing, the category must still exist.
        if ($id !== null && !(new Category())->find((int) $id)) {
            return ['success' => false, 'errors' => [], 'message' => 'This category no longer exists.'];
        }

        // On edit, the id is passed to the unique rule so the category does not clash with itself.
        $ignore = $id !== null ? ',' . (int) $id : '';

        $validate = Validation::validate($request, [
            'name'        => 'required|max:100|unique_active:categories,name' . $ignore,
            'description' => 'max:255',
            'status'      => 'required|in:0,1',
        ]);
        if (!$validate['success']) {
            $validate['message'] = 'Please fix the errors below.';
            return $validate;
        }

        $name        = trim($request['name']);
        $description = trim($request['description'] ?? '');
        $status      = (int) $request['status'];

        $model = new Category();
        if ($id === null) {
            $model->create($name, $description, $status);
            $message = 'Category added.';
        } else {
            $model->update((int) $id, $name, $description, $status);
            $message = 'Category updated.';
        }

        return ['success' => true, 'errors' => [], 'message' => $message];
    }

    // Soft deletes a category, unless research items still use it.
    public static function delete($request){

        $id    = (int) ($request['id'] ?? 0);
        $model = new Category();

        if (!$model->find($id)) {
            return ['success' => false, 'message' => 'This category no longer exists.'];
        }

        $used = $model->countResearch($id);
        if ($used > 0) {
            return ['success' => false, 'message' => "This category is used by {$used} research item(s), so it cannot be deleted."];
        }

        $model->softDelete($id);

        return ['success' => true, 'message' => 'Category deleted.'];
    }

}
