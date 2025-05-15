<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function signIn(Request $request)
    {
        $data = $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        $user = User::where("email", $data["email"])->first();

        if (!$user || !Hash::check($data["password"], $user->password)) {
            return response()->json(
                [
                    "errors" => "Identifiants de connexion incorrects.",
                ],
                401
            );
        }

        $token = $user->createToken("auth_token")->plainTextToken;

        return response()->json([
            "user" => $user,
            "token" => $token,
        ]);
    }

    public function signUp(Request $request)
    {
        try {
            $data = $request->validate(
                [
                    "username" => "required|unique:users",
                    "email" => "required|email|unique:users",
                    "password" => "required",
                    "photo" => "nullable|image|max:2048",
                    Password::min(8)->letters()->numbers()->symbols(),
                ],
                [
                    "username.required" => "Le champ nom d'utilisateur est requis.",
                    "username.unique" => "Ce nom d'utilisateur est déjà pris.",
                    "email.required" => "Le champ email est requis.",
                    "email.email" => "Le format de l'email est invalide.",
                    "email.unique" => "Cet email est déjà utilisé.",
                    "password.required" => "Le champ mot de passe est requis.",
                    "password" =>
                        "Le mot de passe doit comporter 8 caractères, une lettre, un chiffre et un symbole",
                ]
            );

            $data["password"] = Hash::make($data["password"]);

            if ($request->hasFile("photo")) {
                $file = $request->file("photo");
                $filename = "profile_" . uniqid() . "." . $file->getClientOriginalExtension();
                $path = \Storage::disk("minio")->putFileAs("", $file, $filename);
                $data["profile_photo"] = $path;
            }

            $profile_photo_url = null;
            $user = User::create($data);
            $token = $user->createToken("auth_token")->plainTextToken;

            if (!empty($user->profile_photo)) {
                $profile_photo_url = \Storage::disk("minio")->temporaryUrl(
                    $user->profile_photo,
                    now()->addMinutes(60)
                );
            }

            return response()->json([
                "user" => $user,
                "token" => $token,
                "profile_photo_url" => $profile_photo_url,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(
                [
                    "errors" => $e->errors(),
                ],
                422
            );
        }
    }

    /**
     * Logout a User
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function signOut(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            "message" => "Logged out",
        ]);
    }
}
