<template>
  <div
    class="font-poppins min-h-screen flex items-center justify-center bg-gray-50 p-4"
  >
    <div
      class="card bg-base-200 border-base-300 rounded-box border p-6 max-w-xs sm:max-w-sm md:max-w-md w-full shadow-lg"
    >
      <h2 class="text-2xl font-semibold text-center mb-6 text-dark-slate">
        Login
      </h2>

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

const validateEmail = (emailVal) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
const validatePassword = (pw) => /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(pw);

const handleLogin = async () => {
  // Validate inputs
  emailError.value = !validateEmail(email.value);
  passwordError.value = !validatePassword(password.value);

  if (emailError.value || passwordError.value) return;

  try {
    // Call API (applicants login)
    const response = await axios.post(
      "http://127.0.0.1:8000/api/applicants/login",
      {
        emailAddress: email.value,
        password: password.value,
      }
    );

    // Extract user data
    const userData = response.data.user; // applicants login returns user object
    const role = "applicant"; // we know this is always applicant

    const displayName = `${userData.firstName} ${userData.lastName}`;

    // Save user to localStorage
    localStorage.setItem(
      "user",
      JSON.stringify({
        ...userData,
        role,
        displayName,
      })
    );

    // Redirect applicant to Homepage
    router.push({ name: "Homepage" }); // /app
  } catch (err) {
    console.error(err.response?.data || err.message);
    alert("Invalid credentials. Please try again.");
  }
};
</script>
