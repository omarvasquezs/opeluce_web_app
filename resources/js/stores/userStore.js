import { reactive } from 'vue'

// Create a reactive user store
export const userStore = reactive({
  user: null,
  isAuthenticated: false,
  isAdmin: false,

  // Initialize user data from localStorage
  init() {
    const storedUser = localStorage.getItem('user')
    const authToken = localStorage.getItem('auth_token')
    
    if (storedUser && authToken) {
      try {
        this.user = JSON.parse(storedUser)
        this.isAuthenticated = true
        this.isAdmin = this.user.role || false
      } catch (error) {
        console.error('Error parsing user data:', error)
        this.clearUser()
      }
    }
  },

  // Update user data
  setUser(userData, token = null) {
    this.user = userData
    this.isAuthenticated = true
    this.isAdmin = userData.role || false
    
    // Update localStorage
    localStorage.setItem('user', JSON.stringify(userData))
    if (token) {
      localStorage.setItem('auth_token', token)
    }
  },

  // Update user data without changing authentication state
  updateUser(userData) {
    this.user = { ...this.user, ...userData }
    this.isAdmin = this.user.role || false
    
    // Update localStorage
    localStorage.setItem('user', JSON.stringify(this.user))
  },

  // Clear user data
  clearUser() {
    this.user = null
    this.isAuthenticated = false
    this.isAdmin = false
    
    // Clear localStorage
    localStorage.removeItem('user')
    localStorage.removeItem('auth_token')
  },

  // Refresh user data from server
  async refreshUser() {
    if (!this.isAuthenticated) return
    
    try {
      const response = await fetch('/api/user', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          'Accept': 'application/json',
        }
      })
      
      if (response.ok) {
        const userData = await response.json()
        this.updateUser(userData)
      } else if (response.status === 401) {
        // Token expired or invalid
        this.clearUser()
        window.location.href = '/login'
      }
    } catch (error) {
      console.error('Error refreshing user data:', error)
    }
  }
})

// Initialize the store
userStore.init()
