<script setup>
import { useRouter } from "vue-router";

const router = useRouter();
</script>

<template>
  <div class="flex-grow flex items-center justify-between p-4">
    <!-- Logo / Title -->
    <button
      class="text-xl font-bold font-poppins text-darkslategray whitespace-nowrap"
      @click="$router.push({ name: 'Homepage' })"
    >
      Pathfinder
    </button>

    <!-- Search Bar -->
    <label
      class="bg-blue-gray input w-full max-w-md flex items-center gap-2 border-none rounded-full px-3 py-2 ml-4"
    >
      <svg
        class="h-5 w-5 text-gray-500 flex-shrink-0"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
      >
        <g
          stroke-linejoin="round"
          stroke-linecap="round"
          stroke-width="2.5"
          fill="none"
          stroke="darkslategray"
        >
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.3-4.3"></path>
        </g>
      </svg>
      <input
        type="search"
        placeholder="Search"
        required
        class="flex-grow bg-transparent outline-none font-poppins"
      />
    </label>
  </div>
</template>
