<template>
  <div
    class="pb-20 min-h-screen flex items-center justify-center bg-gray-50 p-4"
  >
    <div>
      <div>
        <div class="flex items-center justify-center">
          <p class="p-5 text-2xl font-semibold font-inter text-dark-slate">
            PathFinder
          </p>
        </div>
        <div class="pt-10 pb-2">
          <button
            class="btn btn-ghost btn-xs sm:btn-sm md:btn-md lg:btn-lg xl:btn-xl text-dark-slate"
            @click="$router.push('/loginform')"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 18l-6-6 6-6"
              />
            </svg>
          </button>
        </div>
        <h2
          class="font-inter text-3xl font-semibold text-left mb-6 text-dark-slate pl-3"
        >
          Create Account
        </h2>
      </div>

      <div class="form-control mb-4">
        <input
          class="input w-full"
          type="text"
          required
          placeholder="First Name"
          name="firstName"
        />
      </div>

      <div class="form-control mb-4">
        <input
          class="input w-full"
          type="text"
          placeholder="Middle Name"
          name="middleName"
        />
      </div>

      <div class="form-control mb-4">
        <input
          class="input w-full"
          type="text"
          required
          placeholder="Last Name"
          name="lastName"
        />
      </div>

      <div class="form-control mb-4">
        <input
          class="input w-full"
          type="text"
          required
          placeholder="Address"
          name="address"
        />
      </div>

      <div class="form-control mb-4">
        <input
          class="input validator w-full"
          type="email"
          required
          placeholder="Email"
          name="emailAddress"
        />
        <p class="validator-hint hidden">Invalid Email</p>
      </div>

      <div class="form-control mb-4">
        <input
          type="tel"
          class="input validator tabular-nums w-full"
          required
          placeholder="Phone Number"
          pattern="[0-9]*"
          minlength="11"
          maxlength="11"
          title="Must be 11 digits"
          name="phoneNumber"
        />
        <p class="hidden validator-hint">Must be 11 digits</p>
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
          name="password"
        />
        <p class="validator-hint hidden">
          Must be more than 8 characters, including number, lowercase letter,
          uppercase letter
        </p>
      </div>

      <div class="form-control mb-4">
        <input
          type="password"
          class="input validator input-bordered w-full"
          required
          placeholder="Confirm Password"
          minlength="8"
          pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
          title="Must be more than 8 characters, including number, lowercase letter, uppercase letter"
          name="confirmPassword"
        />
        <p class="validator-hint hidden">Invalid Password</p>
      </div>

      <div>
        <label class="flex items-center space-x-2 cursor-pointer mb-2">
          <input
            type="checkbox"
            class="checkbox validator"
            required
            title="Required"
            v-model="termsAccepted"
          />
          <span>Accept all terms and conditions before creating account</span>
        </label>
      </div>

      <div class="card-actions justify-end pt-4">
        <button class="btn btn-primary w-2/4 bg-dark-slate text-white">
          Create
        </button>
      </div>
    </div>
  </div>
</template>

<script></script>
