<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Profile extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if (!session('username')) {
            return redirect()->to('/login?redirect=' . urlencode((string) current_url()));
        }

        $profile = $this->userModel->getProfileByUsername((string) session('username'));
        if (!$profile) {
            return redirect()->to('/logout');
        }

        $menu = GetMenu();
        $data = [
            'title' => 'My Profile',
            'profile' => $profile,
            'avatarOptions' => $this->getAvatarOptions(),
            'menu' => $menu,
            'menuJson' => json_encode($menu, JSON_UNESCAPED_SLASHES),
            'isMobile' => cekMobile(),
        ];

        return view('profile/index', $data);
    }

    public function changePassword()
    {
        if (!session('username')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Session login tidak ditemukan']);
        }

        $oldPassword = trim((string) $this->request->getVar('old_password'));
        $newPassword = trim((string) $this->request->getVar('new_password'));
        $confirmPassword = trim((string) $this->request->getVar('confirm_password'));

        if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Password lama, password baru, dan konfirmasi wajib diisi']);
        }
        if ($newPassword !== $confirmPassword) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Konfirmasi password baru tidak sama']);
        }
        if (strlen($newPassword) < 4) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Password baru minimal 4 karakter']);
        }
        if (!$this->userModel->verifyPassword((string) session('username'), $oldPassword)) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Password lama tidak sesuai']);
        }

        $ok = $this->userModel->updatePasswordByUsername((string) session('username'), $newPassword);
        if (!$ok) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Gagal mengubah password']);
        }

        tracelog('UPDATE', 'GANTI PASSWORD PROFILE username=' . session('username'));
        return $this->response->setJSON(['tipe' => 'success', 'data' => 'Password berhasil diubah']);
    }

    public function changeAvatar()
    {
        if (!session('username')) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Session login tidak ditemukan']);
        }

        $avatar = trim((string) $this->request->getVar('avatar'));
        $allowed = $this->getAvatarOptions();
        if ($avatar === '' || !in_array($avatar, $allowed, true)) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Avatar yang dipilih tidak valid']);
        }

        $ok = $this->userModel->updateAvatarByUsername((string) session('username'), $avatar);
        if (!$ok) {
            return $this->response->setJSON(['tipe' => 'error', 'data' => 'Gagal mengubah avatar']);
        }

        session()->set('avatar', $avatar);
        tracelog('UPDATE', 'GANTI AVATAR PROFILE username=' . session('username') . ' avatar=' . $avatar);
        return $this->response->setJSON(['tipe' => 'success', 'data' => 'Avatar berhasil diubah', 'avatar' => $avatar]);
    }

    private function getAvatarOptions(): array
    {
        $dir = FCPATH . 'assets/images/profile';
        if (!is_dir($dir)) {
            return [];
        }

        $items = scandir($dir) ?: [];
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $avatars = [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (!is_file($path)) {
                continue;
            }
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExt, true)) {
                $avatars[] = $item;
            }
        }

        sort($avatars);
        return $avatars;
    }
}
