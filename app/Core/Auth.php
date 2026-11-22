<?php
declare(strict_types=1);
namespace App\Core;
use App\Repositories\AdminRepository;
final class Auth
{
    public function __construct(private readonly AdminRepository $admins)
    {
    }
    public function attempt(string $username, string $password): bool
    {
        $admin = $this->admins->findAdminByUsername($username);
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return false;
        }
        Session::put('admin_id', (int) $admin['id']);
        Session::put('admin_username', $admin['username']);
        session_regenerate_id(true);
        return true;
    }
    public function check(): bool
    {
        return Session::has('admin_id');
    }
    public function logout(): void
    {
        Session::forget('admin_id');
        Session::forget('admin_username');
        session_regenerate_id(true);
    }
}