<?php
class CategoryController extends ApiController
{
    public function handle(): never
    {
        $this->guard();
        $this->run(function () {
            $m = Request::method();
            if ($m === 'GET') {
                if (Request::get('all') === '1') {
                    Response::success(Category::activeList());
                }
                if ($id = Request::id()) {
                    Response::success(Category::find($id) ?? throw new ApiException('Category not found.', 404));
                }
                [$page, $per, $off] = Request::paging();
                $order = $this->order(['name' => 'c.category_name', 'status' => 'c.status', 'id' => 'c.category_id'], 'name');
                [$items, $total] = Category::list(Request::like(), Request::get('status') ?: null, $order, $per, $off);
                $this->paged($items, $total, $page, $per);
            }

            $this->admin();   // only administrators change categories

            if ($m === 'POST') {
                $d  = $this->input(null);
                $id = Category::create($d);
                Logger::log('CREATE', 'vehicle_categories', $id, $d['category_name']);
                Response::success(Category::find($id), 'Category added.', 201);
            }
            if ($m === 'PUT') {
                $id = $this->needId();
                if (!Category::find($id)) { throw new ApiException('Category not found.', 404); }
                $d = $this->input($id);
                Category::update($id, $d);
                Logger::log('UPDATE', 'vehicle_categories', $id, $d['category_name']);
                Response::success(Category::find($id), 'Category updated.');
            }
            if ($m === 'DELETE') {
                $id  = $this->needId();
                $cat = Category::find($id) ?? throw new ApiException('Category not found.', 404);
                $n   = Category::usage($id);
                if ($n > 0) {
                    throw new ApiException("Cannot delete: $n vehicle(s) still use this category. Reassign them first or set the category to Inactive.", 409);
                }
                Category::delete($id);
                Logger::log('DELETE', 'vehicle_categories', $id, $cat['category_name']);
                Response::success(null, 'Category deleted.');
            }
            throw new ApiException('Method not allowed.', 405);
        });
    }

    private function input(?int $ignoreId): array
    {
        $v = new Validator(Request::input());
        $d = [
            'category_name' => $v->str('category_name', true, 50),
            'description'   => $v->str('description', false, 2000),
            'status'        => $v->choice('status', ['Active', 'Inactive'], false, 'Active'),
        ];
        $v->fail();
        if (Category::nameExists($d['category_name'], $ignoreId)) {
            throw new ApiException('Please fix the highlighted fields.', 409, ['category_name' => 'This category already exists.']);
        }
        return $d;
    }
}