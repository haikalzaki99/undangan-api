<?php

namespace App\Controllers\Api;

use App\Models\User;
use App\Response\JsonResponse;
use Core\Routing\Controller;
use Core\Http\Request;
use Core\Http\Respond;
use Core\Valid\Hash;
use DateTimeZone;

class OwnerController extends Controller
{
    private $json;

    public function __construct(JsonResponse $json)
    {
        $this->json = $json;
    }

    /**
     * Buat access key unik yang belum terpakai.
     */
    private function generateUniqueKey(): string
    {
        static $generateUnique = null;
        $generateUnique ??= static function () use (&$generateUnique): string {
            $key = Hash::rand(25);

            if (User::where('access_key', $key)->first()->exist()) {
                return $generateUnique();
            }

            return $key;
        };

        return $generateUnique();
    }

    /**
     * Daftarkan klien baru (1 baris users + access_key).
     */
    public function register(Request $request): JsonResponse
    {
        $valid = $this->validate($request, [
            'name' => ['required', 'str', 'trim', 'min:1', 'max:50'],
            'email' => ['required', 'str', 'trim', 'min:5', 'max:55', 'email', 'unik:User:email'],
            'password' => ['nullable', 'str', 'trim', 'min:8', 'max:20'],
            'tz' => ['nullable', 'str', 'trim', 'max:70'],
        ]);

        if ($valid->fails()) {
            return $this->json->errorBadRequest($valid->messages());
        }

        $tz = $valid->get('tz');
        if (!empty($tz) && !in_array($tz, DateTimeZone::listIdentifiers())) {
            return $this->json->errorBadRequest(['Invalid time zone']);
        }

        $password = $valid->get('password');
        if (empty($password)) {
            $password = Hash::rand(5);
        }

        $user = User::create([
            'name' => $valid->get('name'),
            'email' => $valid->get('email'),
            'password' => Hash::make($password),
            'access_key' => $this->generateUniqueKey(),
            'is_filter' => true,
            'can_edit' => true,
            'can_delete' => true,
            'can_reply' => true,
            'is_active' => true,
            'is_confetti_animation' => true,
            'tz' => empty($tz) ? 'Asia/Jakarta' : $tz,
        ]);

        return $this->json->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
            'access_key' => $user->access_key,
            'is_active' => boolval($user->is_active),
        ], Respond::HTTP_CREATED);
    }

    /**
     * Daftar semua klien.
     */
    public function clients(): JsonResponse
    {
        $lists = [];

        foreach (User::orderBy('id', 'DESC')->get() as $user) {
            if ($user->id == 1) {
                continue;
            }

            $lists[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'access_key' => $user->access_key,
                'is_active' => boolval($user->is_active ?? false),
                'created_at' => $user->created_at,
            ];
        }

        return $this->json->successOK($lists);
    }

    /**
     * Update klien: rename, aktif/nonaktif, reset password, rotate access key.
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $valid = $this->validate($request, [
            'name' => ['nullable', 'str', 'trim', 'min:1', 'max:50'],
            'email' => ['nullable', 'str', 'trim', 'min:5', 'max:55', 'email'],
            'password' => ['nullable', 'str', 'trim', 'min:8', 'max:20'],
        ]);

        if ($valid->fails()) {
            return $this->json->errorBadRequest($valid->messages());
        }

        $user = User::find(intval($id));
        if (!$user->exist()) {
            return $this->json->errorNotFound();
        }

        $userId = $user->id;
        $raw = $request->all();
        $newPassword = null;
        $newKey = null;

        if (!empty($valid->get('name'))) {
            $user->name = $valid->get('name');
        }

        if (!empty($valid->get('email'))) {
            $existing = User::where('email', $valid->get('email'))->first();
            if ($existing->exist() && $existing->id != $userId) {
                return $this->json->errorBadRequest(['email sudah digunakan.']);
            }
            $user->email = $valid->get('email');
        }

        if (array_key_exists('is_active', $raw)) {
            $user->is_active = boolval($raw['is_active']);
        }

        if (!empty($valid->get('password'))) {
            $newPassword = $valid->get('password');
            $user->password = Hash::make($newPassword);
        }

        if (boolval($raw['rotate_key'] ?? false)) {
            $newKey = $this->generateUniqueKey();
            $user->access_key = $newKey;
        }

        $status = $user->save();

        if ($status <= 1) {
            return $this->json->successOK([
                'id' => $userId,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => boolval($user->is_active),
                'password' => $newPassword,
                'access_key' => $newKey ?? $user->access_key,
            ]);
        }

        return $this->json->errorServer();
    }

    /**
     * Hapus klien dari database.
     */
    public function delete(string $id): JsonResponse
    {
        $user = User::find(intval($id));
        if (!$user->exist()) {
            return $this->json->errorNotFound();
        }

        if ($user->id == 1) {
            return $this->json->errorBadRequest(['admin tidak bisa dihapus.']);
        }

        $status = $user->destroy();

        if ($status >= 1) {
            return $this->json->successOK(['deleted' => true]);
        }

        return $this->json->errorServer();
    }
}
