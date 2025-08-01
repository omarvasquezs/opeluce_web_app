<template>
  <div id="admin-app">
    <!-- Login Component -->
    <LoginForm 
      v-if="!isAuthenticated" 
      @login-success="handleLoginSuccess"
    />
    
    <!-- Admin Dashboard -->
    <AdminDashboard 
      v-else 
      :user="currentUser"
      @logout="handleLogout"
    />
  </div>
</template>

<script>
import LoginForm from './LoginForm.vue';
import AdminDashboard from './AdminDashboard.vue';

export default {
  name: 'AdminApp',
  components: {
    LoginForm,
    AdminDashboard
  },
  data() {
    return {
      isAuthenticated: false,
      currentUser: null
    };
  },
  methods: {
    handleLoginSuccess(user) {
      this.isAuthenticated = true;
      this.currentUser = user;
      localStorage.setItem('authToken', user.token);
    },
    handleLogout() {
      this.isAuthenticated = false;
      this.currentUser = null;
      localStorage.removeItem('authToken');
    }
  },
  mounted() {
    // Check if user is already authenticated
    const token = localStorage.getItem('authToken');
    if (token) {
      // You can add token validation here
      this.isAuthenticated = true;
    }
  }
};
</script>

<style scoped>
#admin-app {
  font-family: 'Arial', sans-serif;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
