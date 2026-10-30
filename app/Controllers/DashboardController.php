<?php
class DashboardController extends ApiController
{
    public function handle(): never
    {
        $this->guard();
        $this->run(function () {
            if (Request::method() !== 'GET') {
                throw new ApiException('Method not allowed.', 405);
            }
            Response::success([
                'stats'               => Dashboard::stats(),
                'monthly'             => Dashboard::monthly(6),
                'vehicle_status'      => Dashboard::vehicleStatus(),
                'recent_reservations' => Dashboard::recentReservations(5),
                'recent_vehicles'     => Dashboard::recentVehicles(5),
            ]);
        });
    }
}