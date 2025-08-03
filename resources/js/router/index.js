import { createRouter, createWebHistory } from 'vue-router'
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';
import Login from '@/components/LoginForm.vue'
import MainLayout from '@/components/MainLayout.vue'
import Home from '@/views/Home.vue'
import UserList from '@/views/UserList.vue'
import UserForm from '@/views/UserForm.vue'

const routes = [
  { path: '/login', component: Login },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      { path: '', component: Home },
      { 
        path: 'users', 
        component: UserList,
        meta: { requiresAdmin: true }
      },
      { 
        path: 'users/create', 
        component: UserForm,
        meta: { requiresAdmin: true }
      },
      { 
        path: 'users/:id/edit', 
        component: UserForm, 
        props: true,
        meta: { requiresAdmin: true }
      },
      // Admin routes (for backwards compatibility)
      { 
        path: 'admin/users', 
        component: UserList,
        meta: { requiresAdmin: true }
      },
      { 
        path: 'admin/users/create', 
        component: UserForm,
        meta: { requiresAdmin: true }
      },
      { 
        path: 'admin/users/:id/edit', 
        component: UserForm, 
        props: true,
        meta: { requiresAdmin: true }
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// This function is a global navigation guard for the Vue Router.
// It runs before each route change.
router.beforeEach((to, from, next) => {
    // Start the progress bar animation.
    NProgress.start();

    // Retrieve the 'user' and 'auth_token' items from local storage.
    const storedUser = localStorage.getItem('user');
    const authToken = localStorage.getItem('auth_token');

    // Determine if the user is authenticated based on the presence of both 'user' and 'auth_token' in local storage.
    const isAuthenticated = !!(storedUser && authToken);
    
    // Get user role if authenticated
    let userRole = false; // Default to non-admin
    if (isAuthenticated && storedUser) {
        try {
            const userObj = JSON.parse(storedUser);
            userRole = userObj.role || false; // role is boolean: true = admin, false = user
        } catch (error) {
            console.error('Error parsing user data:', error);
        }
    }

    // If the user is authenticated and trying to access the login page, redirect to the home page.
    if (isAuthenticated && to.path === '/login') {
        next('/');
    } 
    // If the user is not authenticated and trying to access any page other than the login page, redirect to the login page.
    else if (!isAuthenticated && to.path !== '/login') {
        next('/login');
    }
    // Check if route requires admin access
    else if (to.meta.requiresAdmin && !userRole) {
        // User is authenticated but not admin, redirect to home
        console.warn('Access denied: Admin role required');
        next('/');
    }
    // Otherwise, allow the navigation to proceed.
    else {
        next();
    }
})

router.afterEach(() => {
    NProgress.done();
})

export default router