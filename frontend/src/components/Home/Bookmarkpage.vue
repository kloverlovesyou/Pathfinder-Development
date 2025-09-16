<script setup>
import { useRouter } from "vue-router";

const router = useRouter();
</script>
<template>
  <main>
    <div class="flex items-center gap-4 mb-4">
      <h2 class="text-2xl font-bold -m-2">Bookmark</h2>
    </div>
    <div class="space-y-4">
      <div class="p-4 bg-blue-gray rounded-lg">
        <div class="flex justify-between items-start">
          <h3 class="font-semibold text-lg">Data Analyst</h3>
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
              <li><a>Remove bookmark</a></li>
              <li><a>Apply</a></li>
            </ul>
          </div>
        </div>

        <p class="text-gray-600 text-sm">November 24, 2025</p>
      </div>
      <div class="p-4 bg-blue-gray rounded-lg">
        <div class="flex justify-between items-start">
          <h3 class="font-semibold text-lg">Branch Sales Supervisor</h3>
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
              <li><a>Remove bookmark</a></li>
              <li><a>Apply</a></li>
            </ul>
          </div>
        </div>

        <p class="text-gray-600 text-sm">September 20, 2025</p>
      </div>
      <div class="p-4 bg-blue-gray rounded-lg">
        <div class="flex justify-between items-start">
          <h3 class="font-semibold text-lg">
            Community Technological Officer (Grade B)
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
              <li><a>Remove bookmark</a></li>
              <li><a>Apply</a></li>
            </ul>
          </div>
        </div>
        <p class="text-gray-600 text-sm">October 24, 2025</p>
      </div>
    </div>
  </main>
</template>
