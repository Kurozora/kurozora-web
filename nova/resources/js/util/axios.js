import axios from 'axios'
import router from '@/router'

const instance = axios.create()

instance.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
instance.defaults.headers.common['X-CSRF-TOKEN'] = document.head.querySelector(
  'meta[name="csrf-token"]'
).content

instance.interceptors.response.use(
  response => response,
  error => {
    if (error instanceof axios.Cancel) {
      return Promise.reject(error)
    }

    const { status } = error.response

    // Show the user a 500 error
    if (status >= 500) {
      Nova.$emit('error', error.response.data.message)
    }

    // Handle Session Timeouts
    if (status === 401) {
      window.location.reload()
    }

    // Handle Forbidden
    if (status === 403) {
      router.push({ name: '403' })
    }

    // Handle Token Timeouts
    if (status === 419) {
      Nova.$emit('token-expired')
    }

    return Promise.reject(error)
  }
)

export default instance
