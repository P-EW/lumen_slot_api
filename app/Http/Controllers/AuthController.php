<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Score;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $email = $request->email;
        $password = $request->password;

        if(empty($email) || empty($password)){
            return response()->json(['statusCode' => 400, 'message' => 'Field missing']);
        }

        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['statusCode' => 401, 'message' => 'Login incorrect']);
        }

        //get credits and roll information
        $user = User::query()->where('email', $request->email)->first();
        $userStats = Score::query()->where('id_user', $user->id)->first();

        return response()->json([
            'statusCode' => 200,
            'message' => 'Login successful',
            'id' => $user->id,
            'credits' => $userStats->credits,
            'rolled' => $userStats->played_rolls,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function register(Request $request)
    {
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        // Check if field is empty
        if (empty($name) or empty($email) or empty($password)) {
            return response()->json(['statusCode' => 400, 'message' => 'You must fill all the fields']);
        }

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['statusCode' => 401, 'message' => 'You must enter a valid email']);
        }

        // Check if password is greater than 7 character
        if (strlen($password) < 8) {
            return response()->json(['statusCode' => 401, 'message' => 'Password should be min 8 character']);
        }

        // check if the 2 passwords matches
        if ($password != $request->password_confirmation) {
            return response()->json(['statusCode' => 401, 'message' => 'Passwords are different']);
        }

        // Check if user already exist
        if (User::where('email', '=', $email)->exists()) {
            return response()->json(['statusCode' => 401, 'message' => 'User already exists with this email']);
        }

        // Create new user
        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = app('hash')->make($request->password);

            $user->save();

            $user = User::where('email', $request->email)->first();
            $score = new Score();
            $score->id_user = $user->id;
            $score->credits = 100;
            $score->played_rolls = 0;
            $score->jackpot_hits = 0;
            $score->save();

	        return response()->json(['statusCode' => 200, 'message' => 'Account created']);
            //if ($user->save()) {
            //    return $this->login($request);
            //}
        } catch (\Exception $e) {
            return response()->json(['statusCode' => 500, 'message' => $e->getMessage()]);
        }
    }
}
