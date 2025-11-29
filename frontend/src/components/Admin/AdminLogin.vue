<template>
  <div class="font-poppins min-h-screen flex items-center justify-center p-4">
    <div class="relative w-full max-w-md">
      <div
        class="card bg-base-200 border-base-300 rounded-box border p-6 shadow-lg transition duration-300"
      >
        <h2 class="text-2xl font-semibold text-center mb-4 text-dark-slate">
          Admin Login
        </h2>

        <form @submit.prevent="handleAdminLogin">
          <!-- Email -->
          <div class="form-control mb-2">
            <input
              v-model="email"
              class="input validator w-full"
              type="email"
              required
              placeholder="Admin Email"
            />
            <p class="validator-hint" v-if="emailError">Invalid Email</p>
          </div>

          <!-- Password -->
          <div class="form-control relative mb-4">
            <input
              :type="showPassword ? 'text' : 'password'"
              class="w-full border rounded-lg input"
              required
              placeholder="Password"
              v-model="password"
            />

            <!-- Toggle Password -->
            <button
              type="button"
              class="absolute right-3 top-3 text-gray-500"
              aria-label="Toggle password visibility"
              @click="showPassword = !showPassword"
            >
              <span v-if="showPassword">
                👁
              </span>
              <span v-else>
                👁‍🗨
              </span>
            </button>
          </div>

          <div class="card-actions justify-center">
            <button
              type="submit"
              class="btn btn-primary w-3/4 bg-dark-slate text-white"
            >
              Log in
            </button>
          </div>
        </form>

        <!-- Error -->
        <p v-if="loginError" class="text-center mt-4 text-red-600 font-medium">
          {{ loginError }}
        </p>

        <!-- Toast (bottom-right) -->
        <div class="toast toast-end toast-top z-50" v-if="toastMessage">
          <div class="alert alert-error text-white">
            <span>{{ toastMessage }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();

const email = ref("");
const password = ref("");
const showPassword = ref(false);
const emailError = ref(false);
const loginError = ref("");
const toastMessage = ref("");

// Email validator
const validateEmail = (emailVal) =>
  /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);

const showToast = (msg) => {
  toastMessage.value = msg;
  setTimeout(() => (toastMessage.value = ""), 3000);
};

const handleAdminLogin = async () => {
  loginError.value = "";
  emailError.value = !validateEmail(email.value);
  if (emailError.value) return;

  try {
    const res = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/admin/login",
      {
        emailAddress: email.value,
        password: password.value,
      }
    );

    const adminData = res.data.admin;
    const token = res.data.token;

    // Save admin login
    localStorage.setItem("admin_token", token);
    localStorage.setItem(
      "user",
      JSON.stringify({ ...adminData, role: "admin" })
    );

    router.push("/admin"); // redirect to admin dashboard
  } catch (err) {
    console.error("Admin login error:", err);

    if (err.response?.status === 401) {
      return showToast("Incorrect email or password.");
    }

    showToast("Login failed. Try again.");
  }
};
</script>

<style>
input::-ms-reveal,
input::-ms-clear {
  display: none;
}
input::-webkit-credentials-auto-fill-button {
  visibility: hidden;
}
</style>