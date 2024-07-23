<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Получение пользователя по ID
    public function getUserById($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    // Создание нового пользователя
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $request['password'] = Hash::make($request['password']);

        $user = User::create($request->all());

        return response()->json($user, 201);
    }

    // Обновление информации о текущем пользователе
    public function updateUser(Request $request, $id)
    {
        // Находим пользователя по ID
        $user = User::findOrFail($id);

        // Валидируем входные данные
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Если передан пароль, хешируем его
        if ($request->has('password')) {
            $request->merge(['password' => Hash::make($request->input('password'))]);
        }

        // Обновляем данные пользователя
        $user->update($request->all());

        return response()->json($user);
    }

    //Удаление пользователя
    public function deleteUser($id)
    {
        // Находим пользователя по ID
        $user = User::findOrFail($id);

        // Удаляем пользователя
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    // Получение информации о текущем пользователе
    public function getCurrentUser(Request $request)
    {
        return response()->json(Auth::user());
    }
}
