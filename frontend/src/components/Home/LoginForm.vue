<template>
  <div
    class="font-poppins min-h-screen flex items-center justify-center bg-gray-50 p-4"
  >
    <div
      class="card bg-base-200 border-base-300 rounded-box border p-6 max-w-xs sm:max-w-sm md:max-w-md w-full shadow-lg"
    >
      <h2 class="text-2xl font-semibold text-center mb-4 text-dark-slate">
        Login
      </h2>

      <!-- 🔴 Error Message -->
      <p v-if="loginError" class="text-red-500 text-sm text-center mb-4">
        {{ loginError }}
      </p>

      <form @submit.prevent="handleLogin">
        <!-- Email -->
        <div class="form-control mb-4">
          <input
            v-model="email"
            class="input validator w-full"
            type="email"
            required
            placeholder="Email"
          />
          <p class="validator-hint" v-if="emailError">Invalid Email</p>
        </div>

        <!-- Password -->
        <div class="form-control mb-4">
          <input
            v-model="password"
            type="password"
            class="input validator input-bordered w-full"
            required
            placeholder="Password"
            minlength="8"
            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
          />
          <p class="validator-hint" v-if="passwordError">Invalid Password</p>
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

      <div class="text-center mt-4">
        <p class="text-sm text-gray-600">
          Don't have an account?
          <router-link to="/typeofaccount" class="text-primary">
            Register here.
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

const router = useRouter();

const email = ref("");
const password = ref("");
const emailError = ref(false);
const passwordError = ref(false);
const loginError = ref(""); // ✅ add error state

const validateEmail = (emailVal) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
const validatePassword = (pw) => /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(pw);

const handleLogin = async () => {
  emailError.value = !validateEmail(email.value);
  passwordError.value = !validatePassword(password.value);
  loginError.value = ""; // clear old errors
  if (emailError.value || passwordError.value) return;

  try {
    const response = await axios.post("http://127.0.0.1:8000/api/login", {
      emailAddress: email.value,
      password: password.value,
    });

    const userData = response.data.user;
    const role = userData.role;

    let displayName = "";
    if (role === "organization") {
      displayName = userData.organizationName || userData.name || "Organization";
    } else {
      displayName = `${userData.firstName} ${userData.lastName}`;
    }

    localStorage.setItem(
      "user",
      JSON.stringify({
        ...userData,
        role,
        displayName,
      })
    );

    if (role === "organization") {
      router.push("/organization");
    } else {
      router.push("/app");
    }
  } catch (err) {
    console.error(err.response?.data || err.message);
    loginError.value = "Invalid credentials. Please try again."; // ✅ show inline message
  }
};
</script>

