<?php

namespace App\Http\Middleware;

use App\Models\SystemConfig;
use Auth, Route, URL;
use Closure;

class Authorize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next,$guard = null)
    {
        $sys = SystemConfig::findOrFail(1);
        if ($sys->down_name == 1)
        {
            echo $sys->down_desc;exit;
        } elseif ($sys->down_name == 2) {
            return redirect()->to($sys->down_desc);
        }

        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('admin/login');
            }
        }
        /* 判断当前用户是否登录或缓存是否过期 */
        $user = Auth::user();
//        if ( ! $user) {
//            return redirect()->to('/auth/logout');
//        }

        /* 判断当前用户是否为超级管理员 */
        if ($user->is_super_admin) {
            return $next($request);
        }

        //获取当前路由
        $active_router = Route::currentRouteName();
        //获取当前用户所有的权限
        $own_routers = $user->role->routers()->pluck('router')->toArray();

        //dd($own_routers);exit;

        /* 获取当前 URL 当前的路由、控制器方法和上一页 */
//        $route = Route::current()->getName();
//        $action = Route::current()->getActionName();
//        $previousUrl = URL::previous();

        if ( ! $request->ajax()) {
            if ($request->getMethod() == 'GET') {
                return $next($request);
            } else {
                if (!in_array($active_router, $own_routers) && !in_array($active_router, ['admin.index']))
                    return respF('您无权操作，请联系超级管理员');
            }

//            if ($request->getMethod() == 'GET') {
//
//                $menus = UserRepository::getUserMenusPermissionsByUserModel($user);
//
//                if ( ! $menus) {
//                    return view('admin.error.403', compact('previousUrl'));
//                }
//
//                if ( ! in_array($route, $menus)) {
//
//                    return view('admin.error.403', compact('previousUrl'));
//                }
//            } else {
//                $actions = UserRepository::getUserActionPermissionsByUserModel($user);
//
//                if ( ! $actions) {
//                    return view('admin.error.403', compact('previousUrl'));
//                }
//
//                if ( ! in_array($action, $actions)) {
//
//                    return view('admin.error.403', compact('previousUrl'));
//                }
//            }
        } else {

            if (!in_array($active_router, $own_routers) && !in_array($active_router, ['admin.index']))
                return responseWrong('您无权操作，请联系超级管理员');

//            $actions = UserRepository::getUserActionPermissionsByUserModel($user);
//
//            if ( ! $actions) {
//                return response()->json(['status' => 0, 'message' => '没有权限执行此操作']);
//            }
//
//            if ( ! in_array($action, $actions)) {
//
//                return response()->json(['status' => 0, 'message' => '没有权限执行此操作']);
//            }
        }

        return $next($request);
    }
}
