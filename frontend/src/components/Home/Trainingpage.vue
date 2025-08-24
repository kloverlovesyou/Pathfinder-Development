<script setup>
import { useRouter } from "vue-router";

const router = useRouter();
</script>
<template>
  <main>
    <div class="flex items-center gap-4 mb-4">
      <button class="group" @click="router.push('/homepage')">
        <svg
          class="size-6 group-hover:hidden"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M9.57 5.93005L3.5 12.0001L9.57 18.0701"
            stroke="#6682A3"
            stroke-width="1.5"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M20.5 12H3.66998"
            stroke="#6682A3"
            stroke-width="1.5"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>

        <svg
          class="size-6 hidden group-hover:block"
          viewBox="0 0 24 24"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M9.57 5.93005L3.5 12.0001L9.57 18.0701"
            stroke="#44576D"
            stroke-width="1.5"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M20.5 12H3.66998"
            stroke="#44576D"
            stroke-width="1.5"
            stroke-miterlimit="10"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </svg>
      </button>
      <svg
        width="32"
        height="32"
        viewBox="0 0 32 32"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M1.40589 1.5621C0 3.12419 0 5.63835 0 10.6667V21.3333C0 26.3616 0 28.8758 1.40589 30.4379C2.81177 32 5.07452 32 9.6 32H22.4C26.9255 32 29.1882 32 30.5941 30.4379C32 28.8758 32 26.3616 32 21.3333V10.6667C32 5.63835 32 3.12419 30.5941 1.5621C29.1882 0 26.9255 0 22.4 0H9.6C5.07452 0 2.81177 0 1.40589 1.5621ZM22.4 8.88889C23.2837 8.88889 24 9.68483 24 10.6667V24.8889C24 25.8707 23.2837 26.6667 22.4 26.6667C21.5163 26.6667 20.8 25.8707 20.8 24.8889V10.6667C20.8 9.68483 21.5163 8.88889 22.4 8.88889ZM11.2 14.2222C11.2 13.2404 10.4837 12.4444 9.6 12.4444C8.71634 12.4444 8 13.2404 8 14.2222L8 24.8889C8 25.8707 8.71634 26.6667 9.6 26.6667C10.4837 26.6667 11.2 25.8707 11.2 24.8889V14.2222ZM17.6 17.7778C17.6 16.7959 16.8837 16 16 16C15.1163 16 14.4 16.7959 14.4 17.7778V24.8889C14.4 25.8707 15.1163 26.6667 16 26.6667C16.8837 26.6667 17.6 25.8707 17.6 24.8889V17.7778Z"
          fill="#44576D"
        />
      </svg>

      <h2 class="text-2xl font-bold -m-2">Training</h2>
    </div>
    <div class="space-y-4">
      <div class="p-4 bg-blue-gray rounded-lg">
        <div class="flex justify-between items-start">
          <h3 class="font-semibold text-lg">
            Professional development in emerging technologies and cognitive
            enhancement
          </h3>
          <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost m-1">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="15"
                height="15"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path
                  d="M12 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"
                />
              </svg>
            </div>
            <ul
              tabindex="0"
              class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
            >
              <li><a>Bookmark</a></li>
              <li><a>Register</a></li>
            </ul>
          </div>
        </div>

        <p class="text-gray-600 text-sm">
          November 24, 2025 | 1:30 PM to 5:00 PM
        </p>
      </div>
      <div class="p-4 bg-blue-gray rounded-lg">
        <div class="flex justify-between items-start">
          <h3 class="font-semibold text-lg">
            Mind Over Machine: Navigating AI in Everyday Life
          </h3>
          <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost m-1">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="15"
                height="15"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path
                  d="M12 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"
                />
              </svg>
            </div>
            <ul
              tabindex="0"
              class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
            >
              <li><a>Bookmark</a></li>
              <li><a>Register</a></li>
            </ul>
          </div>
        </div>

        <p class="text-gray-600 text-sm">
          September 20, 2025 | 7:30 PM to 12:00 PM
        </p>
      </div>
      <div class="p-4 bg-blue-gray rounded-lg">
        <div class="flex justify-between items-start">
          <h3 class="font-semibold text-lg">
            Elevate Your Career: Professional Development Workshop Series
          </h3>
          <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost m-1">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="15"
                height="15"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path
                  d="M12 3a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4zm0 8a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"
                />
              </svg>
            </div>
            <ul
              tabindex="0"
              class="dropdown-content menu bg-base-100 rounded-box z-1 w-52 p-2 shadow-sm"
            >
              <li><a>Bookmark</a></li>
              <li><a>Register</a></li>
            </ul>
          </div>
        </div>
        <p class="text-gray-600 text-sm">July 24, 2025 | 1:30 PM to 5:00 PM</p>
      </div>
    </div>
  </main>
</template>
