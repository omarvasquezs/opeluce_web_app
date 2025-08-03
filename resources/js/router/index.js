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
      { path: 'users', component: UserList },
      { path: 'users/create', component: UserForm },
      { path: 'users/:id/edit', component: UserForm, props: true },
      // Admin routes (for backwards compatibility)
      { path: 'admin/users', component: UserList },
      { path: 'admin/users/create', component: UserForm },
      { path: 'admin/users/:id/edit', component: UserForm, props: true },
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

    // If the user is authenticated and trying to access the login page, redirect to the home page.
    if (isAuthenticated && to.path === '/login') {
        next('/');
    } 
    // If the user is not authenticated and trying to access any page other than the login page, redirect to the login page.
    else if (!isAuthenticated && to.path !== '/login') {
        next('/login');
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