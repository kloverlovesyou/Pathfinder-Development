import { defineStore } from "pinia";
import axios from "axios";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    isLoggedIn: !!localStorage.getItem("token"),
    user: null,
  }),
  actions: {
    async login(token) {
      localStorage.setItem("token", token);
      axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
      this.isLoggedIn = true;
      await this.fetchUser(); // Get user info after login
    },
    logout() {
      localStorage.removeItem("token");
      delete axios.defaults.headers.common["Authorization"];
      this.user = null;
      this.isLoggedIn = false;
    },
    async fetchUser() {
      try {
        const response = await axios.get("/api/user", {
          withCredentials: true,
        });
        this.user = response.data;
      } catch (error) {
        this.logout(); // token invalid, log out
      }
    },
    async initialize() {
      const token = localStorage.getItem("token");
      if (token) {
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
        await this.fetchUser();
      }
    },
  },
});

