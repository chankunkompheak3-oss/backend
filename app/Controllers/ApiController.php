<?php
abstract class ApiController
{
    // Must be logged in; every non-GET request also needs the CSRF header.
    protected function guard(): void
    {
        Auth::requireLogin();
        if (Request::method() !== 'GET') {
            Auth::requireCsrf();
        }
    }

    protected function admin(): void
    {
        Auth::requireRole('Admin');
    }

    // Runs the handler and turns exceptions into clean JSON errors.
    protected function run(callable $fn): never
    {
        try {
            $fn();
        } catch (ApiException $e) {
            Response::error($e->getMessage(), $e->status, $e->errors);
        } catch (PDOException $e) {
            if ($e->getCode() === '45000') {            // our SIGNAL from the triggers
                Response::error($e->errorInfo[2] ?? 'Conflict.', 409);
            }
            if ($e->getCode() === '23000') {            // duplicate / foreign key
                error_log($e->getMessage());
                Response::error('This conflicts with existing data (duplicate value or record still in use).', 409);
            }
            throw $e;                                    // global handler logs it, sends a safe 500
        }
        Response::error('Unsupported request.', 405);
    }

    protected function paged(array $items, int $total, int $page, int $per): never
    {
        Response::success([
            'items' => $items,
            'meta'  => ['page' => $page, 'per_page' => $per, 'total' => $total, 'pages' => max(1, (int)ceil($total / $per))],
        ]);
    }

    // Safe ORDER BY: only whitelisted columns can be used.
    protected function order(array $allowed, string $defaultKey, string $defaultDir = 'ASC'): string
    {
        $key = Request::get('sort');
        $col = $allowed[$key] ?? $allowed[$defaultKey];
        $dir = strtoupper(Request::get('dir') ?: $defaultDir) === 'DESC' ? 'DESC' : 'ASC';
        return "$col $dir";
    }

    protected function needId(): int
    {
        $id = Request::id();
        if ($id <= 0) {
            throw new ApiException('Missing id.', 400);
        }
        return $id;
    }
}