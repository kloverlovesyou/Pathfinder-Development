<script setup>
import applicantImage from "@/assets/images/app.jpg";
import organizationImage from "@/assets/images/org.jpg";
</script>

<template>
  <div
    class="font-poppins min-h-screen pl-10 pr-10 pb-5 flex w-full items-center justify-center bg-gray-50"
  >
    <div class="w-full max-w-6xl px-4 pb-20">
      <div>
        <div class="pt-10 pb-2">
          <button
            class="btn btn-ghost text-dark-slate"
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
          Account Type
        </h2>
      </div>

      <!-- cards -->
      <div class="flex w-full flex-col lg:flex-row gap-6">
        <div
          class="card bg-base-300 rounded-box flex-1 min-h-[200px] p-6"
          @click="$router.push('/auth/aregistration')"
        >
          <figure><img :src="applicantImage" alt="Applicant" /></figure>
          <p
            class="sm:text-base md:text-md lg:text-lg xl:text-xl text-center font-inter pt-4"
          >
            Applicant
          </p>
        </div>
        <div class="divider lg:divider-horizontal font-inter">OR</div>
        <div
          class="card bg-base-300 rounded-box flex-1 min-h-[200px] p-6"
          @click="$router.push('/auth/oregistration')"
        >
          <figure><img :src="organizationImage" alt="Organization" /></figure>
          <p
            class="sm:text-base md:text-md lg:text-lg xl:text-xl text-center font-inter pt-4"
          >
            Organization
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
