import { ref } from 'vue'
import axios from 'axios'

const user = ref(null)
const authenticated = ref(false)

export default function useAuth() {
  const attempt = async () => {
    try {
      const response = await axios.get('/api/user', { withCredentials: true })
      user.value = response.data
      authenticated.value = true
    } catch {
      user.value = null
      authenticated.value = false
    }
  }

  return {
    user,
    authenticated,
    attempt
  }
}