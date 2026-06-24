<?php

namespace Tests\Unit;

use App\Http\Middleware\LibraryManagerMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class Bes3aFacilityAccessTest extends TestCase
{
    #[DataProvider('facilityManagerRoles')]
    public function test_facility_managers_can_access_facility_management(string $role): void
    {
        $response = (new LibraryManagerMiddleware)->handle(
            $this->requestForRole($role),
            fn (): Response => new Response('allowed'),
        );

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('allowed', $response->getContent());
    }

    public function test_city_planner_cannot_access_facility_management(): void
    {
        try {
            (new LibraryManagerMiddleware)->handle(
                $this->requestForRole('city_planner'),
                fn (): Response => new Response('allowed'),
            );

            $this->fail('A city planner was allowed to access facility management.');
        } catch (HttpException $exception) {
            $this->assertSame(Response::HTTP_FORBIDDEN, $exception->getStatusCode());
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function facilityManagerRoles(): array
    {
        return [
            'admin' => ['admin'],
            'library manager' => ['library_manager'],
        ];
    }

    private function requestForRole(string $role): Request
    {
        $user = new User;
        $user->forceFill(['role' => $role]);

        $request = Request::create('/facilities');
        $request->setUserResolver(fn (): User => $user);

        return $request;
    }
}
