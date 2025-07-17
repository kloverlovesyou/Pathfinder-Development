<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div
      class="card bg-base-200 border-base-300 rounded-box border p-6 max-w-xs sm:max-w-sm md:max-w-md w-full shadow-lg"
    >
      <h2 class="text-2xl font-semibold text-center mb-6 text-dark-slate">
        Login
      </h2>

      <div class="form-control mb-4">
        <input
          class="input validator w-full"
          type="email"
          required
          placeholder="Email"
          name="EmailAddress"
        />
        <p class="validator-hint hidden">Invalid Email</p>
      </div>

      <div class="form-control mb-4">
        <input
          type="password"
          class="input validator input-bordered w-full"
          required
          placeholder="Password"
          minlength="8"
          pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
          title="Must be more than 8 characters, including number, lowercase letter, uppercase letter"
          name="Password"
        />
        <p class="validator-hint hidden">Invalid Password</p>
      </div>

      <div class="card-actions justify-center">
        <button class="btn btn-primary w-3/4 bg-dark-slate text-white">
          Log in
        </button>
      </div>
      <div class="text-center mt-4">
        <br />
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

<script setup></script>

<style></style>
