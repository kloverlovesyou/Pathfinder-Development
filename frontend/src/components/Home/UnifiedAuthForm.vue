<template>
  <div class="font-poppins w-full flex items-center justify-center min-h-screen md:min-h-[600px] py-4 md:py-8">
    <!-- Main Card Container -->
    <div class="relative w-full max-w-4xl">
      <div class="bg-white rounded-2xl shadow-2xl relative animated-border">
        <div class="overflow-hidden rounded-2xl">
        <!-- Desktop: Horizontal Layout (side-by-side) -->
        <div class="hidden md:flex relative w-full h-[600px] md:h-[650px] overflow-hidden">
          <!-- Registration Content (Left Side - Gets pushed by colored panel) -->
          <div
            class="absolute inset-y-0 left-0 w-1/2 bg-white z-10 overflow-y-auto max-h-full transition-transform duration-1000 ease-in-out"
            :class="isLogin ? '-translate-x-full' : 'translate-x-0'"
          >
            <!-- Registration Type Selection -->
            <div v-if="!selectedRegistrationType" class="p-8 h-full w-full flex flex-col justify-center">
              <h2 class="text-3xl font-semibold mb-6 text-dark-slate">Registration</h2>
              <p class="text-gray-600 mb-6">Choose your account type to continue</p>
              
              <div class="space-y-4">
                <button
                  @click="selectedRegistrationType = 'applicant'"
                  class="w-full p-6 border-2 border-gray-300 rounded-lg hover:border-customButton hover:bg-gray-50 transition-all text-left"
                >
                  <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-customButton rounded-lg flex items-center justify-center">
                      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-xl font-semibold text-dark-slate">Applicant</h3>
                      <p class="text-gray-600 text-sm">Register as an individual job seeker</p>
                    </div>
                  </div>
                </button>

                <button
                  @click="selectedRegistrationType = 'organization'"
                  class="w-full p-6 border-2 border-gray-300 rounded-lg hover:border-customButton hover:bg-gray-50 transition-all text-left"
                >
                  <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-dark-slate rounded-lg flex items-center justify-center">
                      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-xl font-semibold text-dark-slate">Organization</h3>
                      <p class="text-gray-600 text-sm">Register as a company or organization</p>
                    </div>
                  </div>
                </button>
              </div>
            </div>
          </div>

          <!-- Sliding Colored Panel (Moves from Left to Right, covering Login) -->
          <div
            class="absolute inset-y-0 left-0 w-1/2 bg-gradient-to-br from-customButton to-dark-slate transition-transform duration-1000 ease-in-out z-40 flex items-center justify-center p-8"
            :class="isLogin ? 'translate-x-0' : 'translate-x-full'"
          >
            <div class="text-center text-white">
              <div class="min-h-[4rem] md:min-h-[5rem]">
                <template v-if="isLogin">
                  <h2 class="text-3xl md:text-4xl font-bold mb-4" v-if="welcomeBackHeading || showWelcomeBackHeadingCursor">
                    {{ welcomeBackHeading }}<span class="animate-pulse" v-if="showWelcomeBackHeadingCursor">|</span>
                  </h2>
                  <p class="text-base md:text-lg mb-8 opacity-90" v-if="welcomeBackParagraph || showWelcomeBackParagraphCursor">
                    {{ welcomeBackParagraph }}<span class="animate-pulse" v-if="showWelcomeBackParagraphCursor">|</span>
                  </p>
                </template>
                <template v-else>
                  <h2 class="text-3xl md:text-4xl font-bold mb-4" v-if="helloWelcomeHeading || showHelloWelcomeHeadingCursor">
                    {{ helloWelcomeHeading }}<span class="animate-pulse" v-if="showHelloWelcomeHeadingCursor">|</span>
                  </h2>
                  <p class="text-base md:text-lg mb-8 opacity-90" v-if="helloWelcomeParagraph || showHelloWelcomeParagraphCursor">
                    {{ helloWelcomeParagraph }}<span class="animate-pulse" v-if="showHelloWelcomeParagraphCursor">|</span>
                  </p>
                </template>
              </div>
              <button
                @click="toggleForm"
                class="px-6 md:px-8 py-2 md:py-3 border-2 border-white rounded-lg text-white font-semibold hover:bg-white hover:text-customButton transition-colors duration-300"
              >
                {{ isLogin ? 'Register' : 'Login' }}
              </button>
            </div>
          </div>

          <!-- Login Form Container (Right Side - Gets pushed by colored panel) -->
          <div
            class="absolute inset-y-0 right-0 w-1/2 bg-white transition-transform duration-1000 ease-in-out z-30 flex items-center"
            :class="isLogin ? 'translate-x-0' : 'translate-x-full'"
            style="pointer-events: auto;"
          >
            <div class="p-8 h-full w-full flex flex-col justify-center">
              <!-- Login Form -->
              <div class="w-full">
                <h2 class="text-3xl font-semibold mb-6 text-dark-slate">Login</h2>
                
                <form @submit.prevent="handleLogin" class="space-y-4">
                  <!-- Email -->
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></span>
                    </label>
                    <input
                      v-model="loginForm.email"
                      class="input w-full bg-gray-100 border-gray-300 focus:border-customButton focus:ring-2 focus:ring-customButton"
                      type="email"
                      required
                      placeholder="Email"
                    />
                    <p class="validator-hint text-red-500 text-sm mt-1" v-if="loginForm.emailError">
                      Invalid Email
                    </p>
                  </div>

                  <!-- Password -->
                  <div class="form-control relative">
                    <label class="label">
                      <span class="label-text text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></span>
                    </label>
                    <div class="relative">
                      <input
                        :type="showPassword ? 'text' : 'password'"
                        class="input w-full bg-gray-100 border-gray-300 focus:border-customButton focus:ring-2 focus:ring-customButton pr-10"
                        required
                        placeholder="Password"
                        v-model="loginForm.password"
                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                        title="Must contain at least 8 characters, including a number, a lowercase and an uppercase letter"
                      />
                      <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center text-gray-500 hover:text-dark-slate h-5 w-5"
                        @click="showPassword = !showPassword"
                      >
                      <svg
                        v-if="showPassword"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M15.5799 11.9999C15.5799 13.9799 13.9799 15.5799 11.9999 15.5799C10.0199 15.5799 8.41992 13.9799 8.41992 11.9999C8.41992 10.0199 10.0199 8.41992 11.9999 8.41992C13.9799 8.41992 15.5799 10.0199 15.5799 11.9999Z"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12.0001 20.27C15.5301 20.27 18.8201 18.19 21.1101 14.59C22.0101 13.18 22.0101 10.81 21.1101 9.39997C18.8201 5.79997 15.5301 3.71997 12.0001 3.71997C8.47009 3.71997 5.18009 5.79997 2.89009 9.39997C1.99009 10.81 1.99009 13.18 2.89009 14.59C5.18009 18.19 8.47009 20.27 12.0001 20.27Z"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                      <svg
                        v-else
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M14.5299 9.46992L9.46992 14.5299C8.81992 13.8799 8.41992 12.9899 8.41992 11.9999C8.41992 10.0199 10.0199 8.41992 11.9999 8.41992C12.9899 8.41992 13.8799 8.81992 14.5299 9.46992Z"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M17.8201 5.76998C16.0701 4.44998 14.0701 3.72998 12.0001 3.72998C8.47009 3.72998 5.18009 5.80998 2.89009 9.40998C1.99009 10.82 1.99009 13.19 2.89009 14.6C3.68009 15.84 4.60009 16.91 5.60009 17.77"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M8.41992 19.5299C9.55992 20.0099 10.7699 20.2699 11.9999 20.2699C15.5299 20.2699 18.8199 18.1899 21.1099 14.5899C22.0099 13.1799 22.0099 10.8099 21.1099 9.39993C20.7799 8.87993 20.4199 8.38993 20.0499 7.92993"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M15.5099 12.7C15.2499 14.11 14.0999 15.26 12.6899 15.52"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M9.47 14.53L2 22"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M22 2L14.53 9.47"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                      </button>
                    </div>
                  </div>

                  <!-- Forgot Password Link -->
                  <div class="text-right">
                    <button
                      type="button"
                      @click="showForgotPasswordModal = true"
                      class="text-sm text-customButton hover:underline"
                    >
                      Forgot Password?
                    </button>
                  </div>

                  <!-- Error Message -->
                  <p v-if="loginError" class="text-center text-red-600 font-medium text-sm">
                    {{ loginError }}
                  </p>

                  <!-- Submit Button -->
                  <button
                    type="submit"
                    class="btn w-full bg-dark-slate text-white border-none hover:bg-slate-800 transition-colors disabled:opacity-60 rounded-lg font-semibold"
                    :disabled="isLoading"
                  >
                    <span v-if="isLoading" class="flex items-center gap-2">
                      <svg
                        class="animate-spin h-4 w-4 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle
                          class="opacity-25"
                          cx="12"
                          cy="12"
                          r="10"
                          stroke="currentColor"
                          stroke-width="4"
                        ></circle>
                        <path
                          class="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3 3 3h-4z"
                        ></path>
                      </svg>
                      Logging in...
                    </span>
                    <span v-else>LOG IN</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile: Vertical Layout (portrait devices) -->
        <div class="block md:hidden relative w-full min-h-screen overflow-hidden">
          <!-- Registration Content (Top - Visible when Register is clicked) -->
          <div
            class="absolute inset-x-0 top-0 h-1/2 bg-white z-10 overflow-y-auto transition-transform duration-1000 ease-in-out"
            :class="isLogin ? '-translate-y-full' : 'translate-y-0'"
          >
            <!-- Registration Type Selection -->
            <div v-if="!selectedRegistrationType" class="p-4 sm:p-6 h-full w-full flex flex-col justify-center">
              <h2 class="text-xl sm:text-2xl font-semibold mb-3 sm:mb-4 text-dark-slate">Registration</h2>
              <p class="text-gray-600 mb-3 sm:mb-4 text-xs sm:text-sm">Choose your account type to continue</p>
              
              <div class="space-y-2 sm:space-y-3">
                <button
                  @click="selectedRegistrationType = 'applicant'"
                  class="w-full p-3 sm:p-4 border-2 border-gray-300 rounded-lg hover:border-customButton hover:bg-gray-50 transition-all text-left"
                >
                  <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-customButton rounded-lg flex items-center justify-center flex-shrink-0">
                      <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-base sm:text-lg font-semibold text-dark-slate">Applicant</h3>
                      <p class="text-gray-600 text-xs">Register as an individual job seeker</p>
                    </div>
                  </div>
                </button>

                <button
                  @click="selectedRegistrationType = 'organization'"
                  class="w-full p-3 sm:p-4 border-2 border-gray-300 rounded-lg hover:border-customButton hover:bg-gray-50 transition-all text-left"
                >
                  <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-dark-slate rounded-lg flex items-center justify-center flex-shrink-0">
                      <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-base sm:text-lg font-semibold text-dark-slate">Organization</h3>
                      <p class="text-gray-600 text-xs">Register as a company or organization</p>
                    </div>
                  </div>
                </button>
              </div>
            </div>
          </div>

          <!-- Sliding Colored Panel (Moves from Top to Bottom on mobile) -->
          <div
            class="absolute inset-x-0 top-0 h-1/2 bg-gradient-to-br from-customButton to-dark-slate transition-transform duration-1000 ease-in-out z-40 flex items-center justify-center p-4 sm:p-6"
            :class="isLogin ? 'translate-y-0' : 'translate-y-full'"
          >
            <div class="text-center text-white">
              <div class="min-h-[3rem] sm:min-h-[3.5rem]">
                <template v-if="isLogin">
                  <h2 class="text-xl sm:text-2xl font-bold mb-2 sm:mb-3" v-if="welcomeBackHeading || showWelcomeBackHeadingCursor">
                    {{ welcomeBackHeading }}<span class="animate-pulse" v-if="showWelcomeBackHeadingCursor">|</span>
                  </h2>
                  <p class="text-xs sm:text-sm mb-4 sm:mb-6 opacity-90" v-if="welcomeBackParagraph || showWelcomeBackParagraphCursor">
                    {{ welcomeBackParagraph }}<span class="animate-pulse" v-if="showWelcomeBackParagraphCursor">|</span>
                  </p>
                </template>
                <template v-else>
                  <h2 class="text-xl sm:text-2xl font-bold mb-2 sm:mb-3" v-if="helloWelcomeHeading || showHelloWelcomeHeadingCursor">
                    {{ helloWelcomeHeading }}<span class="animate-pulse" v-if="showHelloWelcomeHeadingCursor">|</span>
                  </h2>
                  <p class="text-xs sm:text-sm mb-4 sm:mb-6 opacity-90" v-if="helloWelcomeParagraph || showHelloWelcomeParagraphCursor">
                    {{ helloWelcomeParagraph }}<span class="animate-pulse" v-if="showHelloWelcomeParagraphCursor">|</span>
                  </p>
                </template>
              </div>
              <button
                @click="toggleForm"
                class="px-4 sm:px-6 py-2 border-2 border-white rounded-lg text-white font-semibold hover:bg-white hover:text-customButton transition-colors duration-300 text-xs sm:text-sm"
              >
                {{ isLogin ? 'Register' : 'Login' }}
              </button>
            </div>
          </div>

          <!-- Login Form Container (Bottom - Visible when Login is active) -->
          <div
            class="absolute inset-x-0 bottom-0 h-1/2 bg-white transition-transform duration-1000 ease-in-out z-30 flex items-center overflow-y-auto"
            :class="isLogin ? 'translate-y-0' : 'translate-y-full'"
          >
            <div class="p-4 sm:p-6 h-full w-full flex flex-col justify-center min-h-0">
              <!-- Login Form -->
              <div class="w-full">
                <h2 class="text-xl sm:text-2xl font-semibold mb-3 sm:mb-4 text-dark-slate">Login</h2>
                
                <form @submit.prevent="handleLogin" class="space-y-2 sm:space-y-3">
                  <!-- Email -->
                  <div class="form-control">
                    <label class="label">
                      <span class="label-text text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></span>
                    </label>
                    <input
                      v-model="loginForm.email"
                      class="input w-full bg-gray-100 border-gray-300 focus:border-customButton focus:ring-2 focus:ring-customButton text-sm"
                      type="email"
                      required
                      placeholder="Email"
                    />
                    <p class="validator-hint text-red-500 text-xs mt-1" v-if="loginForm.emailError">
                      Invalid Email
                    </p>
                  </div>

                  <!-- Password -->
                  <div class="form-control relative">
                    <label class="label">
                      <span class="label-text text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></span>
                    </label>
                    <div class="relative">
                      <input
                        :type="showPassword ? 'text' : 'password'"
                        class="input w-full bg-gray-100 border-gray-300 focus:border-customButton focus:ring-2 focus:ring-customButton pr-10 text-sm"
                        required
                        placeholder="Password"
                        v-model="loginForm.password"
                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                        title="Must contain at least 8 characters, including a number, a lowercase and an uppercase letter"
                      />
                      <button
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center justify-center text-gray-500 hover:text-dark-slate h-5 w-5"
                        @click="showPassword = !showPassword"
                      >
                      <svg
                        v-if="showPassword"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M15.5799 11.9999C15.5799 13.9799 13.9799 15.5799 11.9999 15.5799C10.0199 15.5799 8.41992 13.9799 8.41992 11.9999C8.41992 10.0199 10.0199 8.41992 11.9999 8.41992C13.9799 8.41992 15.5799 10.0199 15.5799 11.9999Z"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12.0001 20.27C15.5301 20.27 18.8201 18.19 21.1101 14.59C22.0101 13.18 22.0101 10.81 21.1101 9.39997C18.8201 5.79997 15.5301 3.71997 12.0001 3.71997C8.47009 3.71997 5.18009 5.79997 2.89009 9.39997C1.99009 10.81 1.99009 13.18 2.89009 14.59C5.18009 18.19 8.47009 20.27 12.0001 20.27Z"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                      <svg
                        v-else
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                      >
                        <path
                          d="M14.5299 9.46992L9.46992 14.5299C8.81992 13.8799 8.41992 12.9899 8.41992 11.9999C8.41992 10.0199 10.0199 8.41992 11.9999 8.41992C12.9899 8.41992 13.8799 8.81992 14.5299 9.46992Z"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M17.8201 5.76998C16.0701 4.44998 14.0701 3.72998 12.0001 3.72998C8.47009 3.72998 5.18009 5.80998 2.89009 9.40998C1.99009 10.82 1.99009 13.19 2.89009 14.6C3.68009 15.84 4.60009 16.91 5.60009 17.77"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M8.41992 19.5299C9.55992 20.0099 10.7699 20.2699 11.9999 20.2699C15.5299 20.2699 18.8199 18.1899 21.1099 14.5899C22.0099 13.1799 22.0099 10.8099 21.1099 9.39993C20.7799 8.87993 20.4199 8.38993 20.0499 7.92993"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M15.5099 12.7C15.2499 14.11 14.0999 15.26 12.6899 15.52"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M9.47 14.53L2 22"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M22 2L14.53 9.47"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                      </button>
                    </div>
                  </div>

                  <!-- Forgot Password Link -->
                  <div class="text-right">
                    <button
                      type="button"
                      @click="showForgotPasswordModal = true"
                      class="text-xs text-customButton hover:underline"
                    >
                      Forgot Password?
                    </button>
                  </div>

                  <!-- Error Message -->
                  <p v-if="loginError" class="text-center text-red-600 font-medium text-xs">
                    {{ loginError }}
                  </p>

                  <!-- Submit Button -->
                  <button
                    type="submit"
                    class="btn w-full bg-dark-slate text-white border-none hover:bg-slate-800 transition-colors disabled:opacity-60 rounded-lg font-semibold text-sm py-2"
                    :disabled="isLoading"
                  >
                    <span v-if="isLoading" class="flex items-center gap-2">
                      <svg
                        class="animate-spin h-4 w-4 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle
                          class="opacity-25"
                          cx="12"
                          cy="12"
                          r="10"
                          stroke="currentColor"
                          stroke-width="4"
                        ></circle>
                        <path
                          class="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4l-3 3 3 3h-4z"
                        ></path>
                      </svg>
                      Logging in...
                    </span>
                    <span v-else>LOG IN</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast toast-end toast-top z-50" v-if="toastMessage">
      <div class="alert alert-error text-white">
        <span>{{ toastMessage }}</span>
      </div>
    </div>

    <!-- Forgot Password Modal -->
    <div
      v-if="showForgotPasswordModal"
      class="fixed inset-0 backdrop-blur-sm bg-black/30 flex items-center justify-center z-50"
      @click.self="closeForgotPasswordModal"
    >
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold text-gray-800">Forgot Password</h3>
          <button
            @click="closeForgotPasswordModal"
            class="text-gray-500 hover:text-gray-700"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>

        <div class="mb-4">
          <p class="text-gray-700 mb-4">
            Enter your email address and we'll send you a link to reset your password.
          </p>
          <div class="form-control mb-4">
            <label class="label">
              <span class="label-text text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></span>
            </label>
            <input
              v-model="forgotPasswordEmail"
              class="input validator w-full bg-gray-100"
              type="email"
              required
              placeholder="Email"
            />
            <p class="validator-hint text-red-500 text-sm mt-1" v-if="forgotPasswordEmailError">
              Invalid Email
            </p>
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <button
            @click="handleForgotPassword"
            :disabled="sendingResetEmail"
            class="btn btn-primary w-full bg-dark-slate text-white disabled:opacity-50"
          >
            <span v-if="sendingResetEmail">Sending...</span>
            <span v-else>Send Reset Link</span>
          </button>
          <button
            @click="closeForgotPasswordModal"
            class="btn btn-outline w-full"
          >
            Cancel
          </button>
        </div>

        <div
          v-if="forgotPasswordMessage"
          class="mt-4 p-3 rounded"
          :class="
            forgotPasswordMessageType === 'success'
              ? 'bg-green-100 text-green-700'
              : 'bg-red-100 text-red-700'
          "
        >
          <p class="text-sm">{{ forgotPasswordMessage }}</p>
        </div>
      </div>
    </div>

    <!-- Email Verification Modal -->
    <div
      v-if="showVerificationModal"
      class="fixed inset-0 backdrop-blur-sm bg-black/30 flex items-center justify-center z-50"
      @click.self="closeVerificationModal"
    >
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold text-gray-800">Verify Your Email</h3>
          <button
            @click="closeVerificationModal"
            class="text-gray-500 hover:text-gray-700"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>

        <div class="mb-4">
          <div class="flex items-center justify-center mb-4">
            <svg
              class="w-16 h-16 text-yellow-500"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
              ></path>
            </svg>
          </div>
          <p class="text-gray-700 mb-2">
            Please verify your email address before logging in.
          </p>
          <p class="text-sm text-gray-600 mb-4">
            We've sent a verification link to <strong>{{ loginForm.email }}</strong
            >. Please check your inbox and click the verification link.
          </p>
          <p class="text-sm text-gray-500 mb-4">
            Didn't receive the email? Check your spam folder or click the button
            below to resend.
          </p>
        </div>

        <div class="flex flex-col gap-2">
          <button
            @click="resendVerificationEmail"
            :disabled="resendingEmail"
            class="btn btn-primary w-full bg-dark-slate text-white disabled:opacity-50"
          >
            <span v-if="resendingEmail">Sending...</span>
            <span v-else>Resend Verification Email</span>
          </button>
          <button
            @click="closeVerificationModal"
            class="btn btn-outline w-full"
          >
            Close
          </button>
        </div>

        <div
          v-if="resendMessage"
          class="mt-4 p-3 rounded"
          :class="
            resendMessageType === 'success'
              ? 'bg-green-100 text-green-700'
              : 'bg-red-100 text-red-700'
          "
        >
          <p class="text-sm">{{ resendMessage }}</p>
        </div>
      </div>
    </div>

    <!-- Registration Form Modal -->
    <div
      v-if="selectedRegistrationType"
      class="fixed inset-0 backdrop-blur-sm bg-black/30 flex items-center justify-center z-50 p-4"
      @click.self="closeRegistrationModal"
    >
      <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="flex-shrink-0 flex justify-between items-center p-4 border-b bg-white">
          <h3 class="text-xl font-semibold text-dark-slate">
            {{ selectedRegistrationType === 'applicant' ? 'Applicant Registration' : 'Organization Registration' }}
          </h3>
          <button
            @click="closeRegistrationModal"
            class="text-gray-500 hover:text-gray-700 transition-colors"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              ></path>
            </svg>
          </button>
        </div>

        <!-- Modal Body - Scrollable -->
        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="selectedRegistrationType === 'applicant'" class="applicant-form-wrapper">
            <component :is="ApplicantRegistration" />
          </div>
          <div v-else class="organization-form-wrapper">
            <component :is="OrganizationRegistration" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import axios from "axios";
import { useRegistrationStore } from "@/stores/registrationStore";
import ApplicantRegistration from "./A_RegistrationForm.vue";
import OrganizationRegistration from "./O_RegistrationForm.vue";
import { useTypingAnimation } from "@/composables/useTypingAnimation";
import { useSequentialTypingAnimation } from "@/composables/useSequentialTypingAnimation";

const regStore = useRegistrationStore();
const router = useRouter();
const route = useRoute();

// Form state - default to login view
const isLogin = ref(true);
const selectedRegistrationType = ref(null); // 'applicant', 'organization', or null

// Watch route changes to handle direct navigation
onMounted(() => {
  // If route has mode=register query param, show registration
  if (route.query.mode === 'register') {
    isLogin.value = false;
  }
  
  // Initialize typing animations based on initial state
  // Only start heading animations - paragraph animations will start automatically after heading completes
  if (isLogin.value) {
    welcomeBackAnimation.start();
  } else {
    helloWelcomeAnimation.start();
  }
});
const showPassword = ref(false);
const isLoading = ref(false);

// Login form
const loginForm = ref({
  email: "",
  password: "",
  emailError: false,
});

const loginError = ref("");
const toastMessage = ref("");

// Forgot Password
const showForgotPasswordModal = ref(false);
const forgotPasswordEmail = ref("");
const forgotPasswordEmailError = ref(false);
const sendingResetEmail = ref(false);
const forgotPasswordMessage = ref("");
const forgotPasswordMessageType = ref("");

// Email Verification
const showVerificationModal = ref(false);
const resendingEmail = ref(false);
const resendMessage = ref("");
const resendMessageType = ref("");

// Sequential typing animations - heading types first, then paragraph, then deletes in reverse
const welcomeBackAnimation = useSequentialTypingAnimation(
  "Welcome Back!", 
  "Don't have an Account?", 
  { 
    typingSpeed: 120, 
    deletingSpeed: 60, 
    pauseAfterHeading: 800, 
    pauseAfterParagraph: 2500, 
    pauseAfterDelete: 600, 
    autoStart: false
  }
);

const helloWelcomeAnimation = useSequentialTypingAnimation(
  "Hello, Welcome", 
  "Already have an Account?", 
  { 
    typingSpeed: 120, 
    deletingSpeed: 60, 
    pauseAfterHeading: 800, 
    pauseAfterParagraph: 2500, 
    pauseAfterDelete: 600, 
    autoStart: false
  }
);

// Computed properties to unwrap refs and get string values
const welcomeBackHeading = computed(() => {
  try {
    const ref = welcomeBackAnimation.headingText;
    if (!ref) return '';
    const text = ref.value !== undefined ? ref.value : (typeof ref === 'string' ? ref : '');
    return typeof text === 'string' ? text : '';
  } catch (e) {
    return '';
  }
});

const welcomeBackParagraph = computed(() => {
  try {
    const ref = welcomeBackAnimation.paragraphText;
    if (!ref) return '';
    const text = ref.value !== undefined ? ref.value : (typeof ref === 'string' ? ref : '');
    return typeof text === 'string' ? text : '';
  } catch (e) {
    return '';
  }
});

const helloWelcomeHeading = computed(() => {
  try {
    const ref = helloWelcomeAnimation.headingText;
    if (!ref) return '';
    const text = ref.value !== undefined ? ref.value : (typeof ref === 'string' ? ref : '');
    return typeof text === 'string' ? text : '';
  } catch (e) {
    return '';
  }
});

const helloWelcomeParagraph = computed(() => {
  try {
    const ref = helloWelcomeAnimation.paragraphText;
    if (!ref) return '';
    const text = ref.value !== undefined ? ref.value : (typeof ref === 'string' ? ref : '');
    return typeof text === 'string' ? text : '';
  } catch (e) {
    return '';
  }
});

// Get phase information
const welcomeBackPhase = computed(() => {
  try {
    const ref = welcomeBackAnimation.phase;
    if (!ref) return '';
    return ref.value !== undefined ? ref.value : (typeof ref === 'string' ? ref : '');
  } catch (e) {
    return '';
  }
});

const helloWelcomePhase = computed(() => {
  try {
    const ref = helloWelcomeAnimation.phase;
    if (!ref) return '';
    return ref.value !== undefined ? ref.value : (typeof ref === 'string' ? ref : '');
  } catch (e) {
    return '';
  }
});

// Check if typing is in progress
const isWelcomeBackTyping = computed(() => {
  try {
    const ref = welcomeBackAnimation.isTyping;
    if (!ref) return false;
    return ref.value !== undefined ? ref.value : (ref === true);
  } catch (e) {
    return false;
  }
});

const isHelloWelcomeTyping = computed(() => {
  try {
    const ref = helloWelcomeAnimation.isTyping;
    if (!ref) return false;
    return ref.value !== undefined ? ref.value : (ref === true);
  } catch (e) {
    return false;
  }
});

// Check if heading should show cursor (only when typing heading phase)
const showWelcomeBackHeadingCursor = computed(() => {
  return welcomeBackPhase.value === 'heading' && welcomeBackHeading.value.length > 0;
});

const showHelloWelcomeHeadingCursor = computed(() => {
  return helloWelcomePhase.value === 'heading' && helloWelcomeHeading.value.length > 0;
});

// Check if paragraph should show cursor (only when typing paragraph phase)
const showWelcomeBackParagraphCursor = computed(() => {
  return welcomeBackPhase.value === 'paragraph' && welcomeBackParagraph.value.length > 0;
});

const showHelloWelcomeParagraphCursor = computed(() => {
  return helloWelcomePhase.value === 'paragraph' && helloWelcomeParagraph.value.length > 0;
});

// Watch isLogin to restart animations when switching
watch(isLogin, () => {
  // Small delay to ensure smooth transition
  setTimeout(() => {
    if (isLogin.value) {
      welcomeBackAnimation.start();
      helloWelcomeAnimation.stop();
    } else {
      helloWelcomeAnimation.start();
      welcomeBackAnimation.stop();
    }
  }, 500);
});

const validateEmail = (emailVal) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal);
const validatePassword = (pw) => /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/.test(pw);

const toggleForm = () => {
  isLogin.value = !isLogin.value;
};

const showToast = (msg) => {
  toastMessage.value = msg;
  setTimeout(() => {
    toastMessage.value = "";
  }, 3000);
};

const handleLogin = async () => {
  loginForm.value.emailError = !validateEmail(loginForm.value.email);
  const passwordError = !validatePassword(loginForm.value.password);
  loginError.value = "";
  
  if (loginForm.value.emailError || passwordError) return;

  isLoading.value = true;

  try {
    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/login",
      {
        emailAddress: loginForm.value.email,
        password: loginForm.value.password,
      }
    );

    const userData = response.data.user || response.data.organization;
    const token = response.data.token;
    const role =
      userData.role || (userData.adminID ? "organization" : "applicant");

    // Handle rejected status with reason
    if (role === "organization" && userData.status === "rejected") {
      const reason = response.data.reason;
      showToast(`Your registration was rejected. Reason: ${reason}`);
      return;
    }

    let displayName = "";
    if (role === "organization") {
      displayName =
        userData.organizationName || userData.name || "Organization";
    } else if (role === "admin") {
      displayName = userData.name || "Admin";
    } else {
      displayName = `${userData.firstName} ${userData.lastName}`;
    }

    // Save token + user
    localStorage.setItem("token", token);
    localStorage.setItem(
      "user",
      JSON.stringify({ ...userData, role, displayName })
    );

    await regStore.fetchMyRegistrations();

    // Redirect
    if (role === "organization") {
      router.push("/organization");
    } else if (role === "admin") {
      router.push("/admin");
    } else {
      router.push("/app");
    }
  } catch (err) {
    console.error("FULL ERROR RESPONSE:", err);

    if (err.response?.status === 403) {
      let data = err.response.data;
      const reason = data && typeof data === "object" ? data.reason : undefined;
      const msg = reason
        ? `Your registration was rejected. Reason: ${reason}`
        : data.message || "Your registration is not approved.";
      showToast(msg);
    } else if (err.response?.status === 401) {
      // Check if it's an unverified email error
      if (err.response.data?.message?.includes("verify") || err.response.data?.message?.includes("verification")) {
        showVerificationModal.value = true;
      } else {
        showToast("Invalid credentials. Please try again.");
      }
    } else {
      showToast("Invalid credentials. Please try again.");
    }
  } finally {
    isLoading.value = false;
  }
};

const closeForgotPasswordModal = () => {
  showForgotPasswordModal.value = false;
  forgotPasswordEmail.value = "";
  forgotPasswordEmailError.value = false;
  forgotPasswordMessage.value = "";
  forgotPasswordMessageType.value = "";
};

const handleForgotPassword = async () => {
  forgotPasswordEmailError.value = !validateEmail(forgotPasswordEmail.value);
  if (forgotPasswordEmailError.value) return;

  sendingResetEmail.value = true;
  forgotPasswordMessage.value = "";
  forgotPasswordMessageType.value = "";

  try {
    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/forgot-password",
      {
        emailAddress: forgotPasswordEmail.value,
      }
    );

    if (response.data.status === "success") {
      forgotPasswordMessage.value = response.data.message;
      forgotPasswordMessageType.value = "success";
      setTimeout(() => {
        forgotPasswordEmail.value = "";
      }, 2000);
    } else if (response.data.status === "error") {
      forgotPasswordMessage.value = response.data.message;
      forgotPasswordMessageType.value = "error";
    }
  } catch (err) {
    console.error("Forgot password error:", err);
    forgotPasswordMessage.value =
      err.response?.data?.message ||
      "Failed to send reset email. Please try again.";
    forgotPasswordMessageType.value = "error";
  } finally {
    sendingResetEmail.value = false;
  }
};

const closeVerificationModal = () => {
  showVerificationModal.value = false;
  resendMessage.value = "";
  resendMessageType.value = "";
};

const closeRegistrationModal = () => {
  selectedRegistrationType.value = null;
};

const resendVerificationEmail = async () => {
  resendingEmail.value = true;
  resendMessage.value = "";
  resendMessageType.value = "";

  try {
    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/resend-verification",
      {
        emailAddress: loginForm.value.email,
      }
    );

    if (response.data.status === "success") {
      resendMessage.value = response.data.message || "Verification email sent successfully!";
      resendMessageType.value = "success";
    } else {
      resendMessage.value = response.data.message || "Failed to resend verification email.";
      resendMessageType.value = "error";
    }
  } catch (err) {
    console.error("Resend verification error:", err);
    resendMessage.value =
      err.response?.data?.message ||
      "Failed to resend verification email. Please try again.";
    resendMessageType.value = "error";
  } finally {
    resendingEmail.value = false;
  }
};
</script>

<style scoped>
/* Hide Chrome/Edge/Safari built-in "eye" icon */
input::-ms-reveal,
input::-ms-clear {
  display: none;
}

input::-webkit-credentials-auto-fill-button {
  visibility: hidden;
}

/* Smooth transitions */
.transition-transform {
  transition: transform 1s ease-in-out;
}

/* Override applicant registration form styles when embedded */
.applicant-form-wrapper :deep(> div.min-h-screen) {
  min-height: auto !important;
  height: auto !important;
  overflow: visible !important;
  display: block !important;
  justify-content: flex-start !important;
  padding: 0 !important;
}

.applicant-form-wrapper :deep(> div > div) {
  width: 100% !important;
  max-width: 100% !important;
  padding: 0 !important;
}

.applicant-form-wrapper :deep(.pt-4.pb-2) {
  display: none !important;
}

.applicant-form-wrapper :deep(h2.text-3xl) {
  display: none !important;
}

/* Match organization form margins - ensure form controls have consistent spacing */
.applicant-form-wrapper :deep(.form-control) {
  margin-bottom: 1rem !important;
}

/* Override organization registration form styles when embedded */
.organization-form-wrapper :deep(> div) {
  padding: 0 !important;
  padding-bottom: 0 !important;
  min-height: auto !important;
  height: auto !important;
  display: block !important;
  justify-content: flex-start !important;
  width: 100% !important;
  max-width: 100% !important;
}

.organization-form-wrapper :deep(> div > div) {
  width: 100% !important;
  padding: 0 !important;
}

.organization-form-wrapper :deep(h2.text-3xl) {
  display: none !important;
}

/* Match organization form margins - ensure form controls have consistent spacing */
.organization-form-wrapper :deep(.form-control) {
  margin-bottom: 1rem !important;
}

/* Animated running light border effect */
.animated-border {
  position: relative;
  padding: 4px;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899, #f59e0b, #3b82f6);
  background-size: 400% 100%;
  border-radius: 1rem;
  animation: borderFlow 4s ease infinite;
}

.animated-border > div {
  background: white;
  border-radius: calc(1rem - 4px);
  width: 100%;
  position: relative;
  z-index: 1;
}

@keyframes borderFlow {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}
</style>

