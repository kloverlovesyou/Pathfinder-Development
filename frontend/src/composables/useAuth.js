import { ref } from "vue";
import axios from "axios";

const user = ref(null);
const authenticated = ref(false);
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

export default function useAuth() {
  const attempt = async () => {
    try {
      const response = await axios.get(`${API_BASE_URL}/user`, {
        withCredentials: true,
      });
      user.value = response.data;
      authenticated.value = true;
    } catch {
      user.value = null;
      authenticated.value = false;
    }
  };

  return {
    user,
    authenticated,
    attempt,
  };
}