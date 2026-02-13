<?php

namespace App\Support;

use Illuminate\Http\Request;

class RedirectBackTo
{
    public static function respond(Request $request, string $fallbackRoute, array $routeParams = [], string $message = 'Guardado exitosamente.', array $extra = [])
    {
        $redirectTo = $request->input('redirect_to');

        if ($request->boolean('return_json')) {
            return response()->json(array_merge([
                'success' => true,
                'message' => $message,
            ], $extra));
        }

        if ($redirectTo) {
            return redirect($redirectTo)->with('success', $message);
        }

        return redirect()->route($fallbackRoute, $routeParams)->with('success', $message);
    }
}
