<?php
class VehicleController extends ApiController
{
    const MAX_IMAGES = 10;

    public function handle(): never
    {
        $this->guard();
        $this->run(function () {
            $m = Request::method();
            if ($m === 'GET') {
                if ($id = Request::id()) {
                    Response::success(Vehicle::find($id) ?? throw new ApiException('Vehicle not found.', 404));
                }
                [$page, $per, $off] = Request::paging();
                $f = ['like' => Request::like(), 'category_id' => Request::get('category_id'), 'fuel_type' => Request::get('fuel_type'),
                      'transmission' => Request::get('transmission'), 'status' => Request::get('status')];
                [$items, $total] = Vehicle::list($f, $this->order(Vehicle::SORT, 'created', 'DESC'), $per, $off);
                $this->paged($items, $total, $page, $per);
            }
            if ($m === 'POST')   { $this->create(); }
            if ($m === 'PUT')    { $this->update($this->needId()); }
            if ($m === 'DELETE') { $this->admin(); $this->remove($this->needId()); }
            throw new ApiException('Method not allowed.', 405);
        });
    }

    private function fields(?int $ignoreId): array
    {
        $v = new Validator(Request::input());
        $year = (int)date('Y') + 1;
        $d = [
            'category_id'         => $v->whole('category_id', false, 1),
            'brand'               => $v->str('brand', true, 50),
            'model'               => $v->str('model', true, 50),
            'year'                => $v->whole('year', false, 1980, $year),
            'color'               => $v->str('color', false, 30),
            'fuel_type'           => $v->str('fuel_type', false, 30),
            'transmission'        => $v->str('transmission', false, 30),
            'seat_capacity'       => $v->whole('seat_capacity', false, 1, 60),
            'license_plate'       => $v->str('license_plate', true, 50),
            'daily_price'         => $v->money('daily_price', true),
            'mileage_km'          => $v->whole('mileage_km', false, 0, 10000000) ?? 0,
            'next_service_date'   => $v->day('next_service_date'),
            'next_service_km'     => $v->whole('next_service_km', false, 0, 10000000),
            'insurance_expiry'    => $v->day('insurance_expiry'),
            'registration_expiry' => $v->day('registration_expiry'),
            'status'              => $v->choice('status', ['Available', 'Reserved', 'Rented', 'Maintenance', 'Out Of Service'], false, 'Available'),
        ];
        $v->fail();
        $errors = [];
        if ($d['category_id'] && !Vehicle::categoryExists($d['category_id'])) { $errors['category_id'] = 'Category does not exist.'; }
        if (Vehicle::plateExists($d['license_plate'], $ignoreId)) { $errors['license_plate'] = 'This license plate is already registered.'; }
        if ($errors) { throw new ApiException('Please fix the highlighted fields.', 409, $errors); }
        return $d;
    }

    private function create(): never
    {
        $d = $this->fields(null);
        $files = Upload::files('images');
        if (count($files) > self::MAX_IMAGES) { throw new ApiException('You can upload at most ' . self::MAX_IMAGES . ' images.', 422, ['images' => 'Too many images.']); }
        foreach ($files as $f) { Upload::check($f); }          // validate everything BEFORE touching the database

        $pdo = Database::connect();
        $saved = [];
        $pdo->beginTransaction();
        try {
            $id = Vehicle::insert($d);
            foreach ($files as $i => $f) {
                $path = Upload::saveImage($f);
                $saved[] = $path;
                VehicleImage::add($id, $path, $i);
            }
            VehicleImage::ensurePrimary($id, null);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            foreach ($saved as $p) { Upload::deleteFile($p); }  // no orphan files
            throw $e;
        }
        Logger::log('CREATE', 'vehicles', $id, $d['license_plate']);
        Response::success(Vehicle::find($id), 'Vehicle added.', 201);
    }

    private function update(int $id): never
    {
        if (!Vehicle::find($id)) { throw new ApiException('Vehicle not found.', 404); }
        $d = $this->fields($id);
        $in = Request::input();
        $remove  = is_array($in['remove_images'] ?? null) ? $in['remove_images'] : [];
        $primary = isset($in['primary_image_id']) && ctype_digit((string)$in['primary_image_id']) ? (int)$in['primary_image_id'] : null;
        $files = Upload::files('images');
        foreach ($files as $f) { Upload::check($f); }
        if (VehicleImage::count($id) - count($remove) + count($files) > self::MAX_IMAGES) {
            throw new ApiException('A vehicle can have at most ' . self::MAX_IMAGES . ' images.', 422, ['images' => 'Too many images.']);
        }

        $pdo = Database::connect();
        $saved = []; $toDelete = [];
        $pdo->beginTransaction();
        try {
            Vehicle::update($id, $d);
            $toDelete = VehicleImage::removeIds($id, $remove);
            $next = VehicleImage::count($id);
            foreach ($files as $i => $f) {
                $path = Upload::saveImage($f);
                $saved[] = $path;
                VehicleImage::add($id, $path, $next + $i);
            }
            VehicleImage::ensurePrimary($id, $primary);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            foreach ($saved as $p) { Upload::deleteFile($p); }
            throw $e;
        }
        foreach ($toDelete as $p) { Upload::deleteFile($p); }   // delete files only after the DB commit succeeded
        Logger::log('UPDATE', 'vehicles', $id, $d['license_plate']);
        Response::success(Vehicle::find($id), 'Vehicle updated.');
    }

    private function remove(int $id): never
    {
        $v = Vehicle::find($id) ?? throw new ApiException('Vehicle not found.', 404);
        if (Vehicle::dependencies($id) > 0) {
            throw new ApiException("This vehicle has reservation, rental or maintenance history and cannot be deleted. Set its status to 'Out Of Service' instead.", 409);
        }
        $paths = VehicleImage::paths($id);
        Vehicle::delete($id);                                   // vehicle_images rows go with it (ON DELETE CASCADE)
        foreach ($paths as $p) { Upload::deleteFile($p); }
        Logger::log('DELETE', 'vehicles', $id, $v['license_plate']);
        Response::success(null, 'Vehicle deleted.');
    }
}