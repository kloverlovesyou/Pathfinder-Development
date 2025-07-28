<script setup>
import { useAuthStore } from "@/stores/auth";
import { ref } from "vue";
import { useRouter } from "vue-router";

const auth = useAuthStore();
const router = useRouter();
const menuClicked = ref(false);

function handleLogout() {
  auth.logout();
  router.push("/loginform");
}

function menuIsClicked() {
  menuClicked.value = !menuClicked.value;
  console.log("Menu clicked:", menuClicked.value);
}
</script>

<template>
  <div class="w-full h-full" :class="{ 'bg-dark-slate': menuClicked }">
    <!-- Navbar -->
    <div class="navbar flex-col lg:flex-row items-center justify-between gap-2">
      <!-- Mobile View: Logo, Menu & Logout in One Row -->
      <div class="w-full flex items-center justify-between lg:hidden">
        <!-- Mobile Menu Button -->
        <div class="dropdown">
          <button tabindex="0" class="btn btn-ghost" @click="menuIsClicked">
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M7 9V7H21V9H7ZM7 13V11H21V13H7ZM7 17V15H21V17H7ZM4 9C3.71667 9 3.47917 8.90417 3.2875 8.7125C3.09583 8.52083 3 8.28333 3 8C3 7.71667 3.09583 7.47917 3.2875 7.2875C3.47917 7.09583 3.71667 7 4 7C4.28333 7 4.52083 7.09583 4.7125 7.2875C4.90417 7.47917 5 7.71667 5 8C5 8.28333 4.90417 8.52083 4.7125 8.7125C4.52083 8.90417 4.28333 9 4 9ZM4 13C3.71667 13 3.47917 12.9042 3.2875 12.7125C3.09583 12.5208 3 12.2833 3 12C3 11.7167 3.09583 11.4792 3.2875 11.2875C3.47917 11.0958 3.71667 11 4 11C4.28333 11 4.52083 11.0958 4.7125 11.2875C4.90417 11.4792 5 11.7167 5 12C5 12.2833 4.90417 12.5208 4.7125 12.7125C4.52083 12.9042 4.28333 13 4 13ZM4 17C3.71667 17 3.47917 16.9042 3.2875 16.7125C3.09583 16.5208 3 16.2833 3 16C3 15.7167 3.09583 15.4792 3.2875 15.2875C3.47917 15.0958 3.71667 15 4 15C4.28333 15 4.52083 15.0958 4.7125 15.2875C4.90417 15.4792 5 15.7167 5 16C5 16.2833 4.90417 16.5208 4.7125 16.7125C4.52083 16.9042 4.28333 17 4 17Z"
                fill="#44576D"
              />
            </svg>
          </button>
          <ul
            tabindex="0"
            class="dropdown-content bg-dark-slate left-0 w-screen mt-3 z-[1] p-4 shadow text-white justify-evenly flex gap-4"
          >
            <li class="flex flex-col items-center">
              <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M22.5882 22.5V21H21.1765V12H22.5882V10.5H18.3529V12H19.7647V21H15.5294V12H16.9412V10.5H12.7059V12H14.1176V21H9.88235V12H11.2941V10.5H7.05882V12H8.47059V21H4.23529V12H5.64706V10.5H1.41176V12H2.82353V21H1.41176V22.5H0V24H24V22.5H22.5882Z"
                  fill="white"
                />
                <path
                  d="M11.2941 0H12.7059L24 7.5V9H0V7.5L11.2941 0Z"
                  fill="white"
                />
              </svg>

              <a>Organization</a>
            </li>
            <li class="flex flex-col items-center">
              <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M1.05442 1.17157C0 2.34315 0 4.22876 0 8V16C0 19.7712 0 21.6569 1.05442 22.8284C2.10883 24 3.80589 24 7.2 24H16.8C20.1941 24 21.8912 24 22.9456 22.8284C24 21.6569 24 19.7712 24 16V8C24 4.22876 24 2.34315 22.9456 1.17157C21.8912 0 20.1941 0 16.8 0H7.2C3.80589 0 2.10883 0 1.05442 1.17157ZM16.8 6.66667C17.4627 6.66667 18 7.26362 18 8V18.6667C18 19.403 17.4627 20 16.8 20C16.1373 20 15.6 19.403 15.6 18.6667V8C15.6 7.26362 16.1373 6.66667 16.8 6.66667ZM8.4 10.6667C8.4 9.93029 7.86274 9.33333 7.2 9.33333C6.53726 9.33333 6 9.93029 6 10.6667L6 18.6667C6 19.403 6.53726 20 7.2 20C7.86274 20 8.4 19.403 8.4 18.6667L8.4 10.6667ZM13.2 13.3333C13.2 12.597 12.6627 12 12 12C11.3373 12 10.8 12.597 10.8 13.3333V18.6667C10.8 19.403 11.3373 20 12 20C12.6627 20 13.2 19.403 13.2 18.6667V13.3333Z"
                  fill="white"
                />
              </svg>

              <a>Training</a>
            </li>
            <li class="flex flex-col items-center">
              <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M20.1456 19.0508C20.4307 19.3614 20.9281 19.3788 21.2121 19.0672C22.3383 17.8319 23.1617 16.3635 23.611 14.7685C24.1117 12.991 24.1277 11.1277 23.6669 9.35319C23.5618 8.94823 23.1083 8.74338 22.7073 8.89627L14.9063 11.8712C14.4209 12.0562 14.2739 12.6531 14.6205 13.0308L20.1456 19.0508Z"
                  fill="white"
                />
                <path
                  d="M22.4984 6.57385C22.7184 6.94044 22.5324 7.4006 22.1252 7.55588L14.4062 10.4995C13.9141 10.6872 13.3816 10.3356 13.3816 9.82305V1.91852C13.3816 1.49848 13.7483 1.16366 14.1778 1.21988C15.9607 1.45324 17.6679 2.08798 19.154 3.08041C20.5314 4.0003 21.6708 5.19538 22.4984 6.57385Z"
                  fill="white"
                />
                <path
                  d="M11.8816 0.706916C11.8816 0.288745 11.518 -0.0452101 11.0897 0.00501095C9.54973 0.185579 8.05327 0.635662 6.67712 1.33692C4.84512 2.27048 3.28289 3.62037 2.12882 5.26701C0.974739 6.91366 0.264437 8.80624 0.0608279 10.7771C-0.142781 12.748 0.166586 14.7363 0.961528 16.566C1.75647 18.3956 3.01245 20.0101 4.61821 21.2665C6.22397 22.5228 8.12995 23.3823 10.1673 23.7686C12.2047 24.155 14.3106 24.0563 16.2984 23.4815C17.5139 23.1299 18.6624 22.607 19.7071 21.9335C20.0707 21.6991 20.1185 21.2086 19.8283 20.8924L12.0997 12.4716C11.9592 12.3185 11.8816 12.1208 11.8816 11.916V0.706916Z"
                  fill="white"
                />
                <path
                  d="M21.0922 20.082C21.1317 20.1251 21.171 20.0986 21.1286 20.0582L20.6621 19.6135L21.0922 20.082Z"
                  fill="white"
                />
              </svg>

              <a>Career</a>
            </li>
            <li class="flex flex-col items-center">
              <svg
                width="26"
                height="27"
                viewBox="0 0 26 27"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M2.16669 9.625C2.16669 7.73938 2.16669 6.79657 2.75247 6.21079C3.33826 5.625 4.28107 5.625 6.16669 5.625H19.8334C21.719 5.625 22.6618 5.625 23.2476 6.21079C23.8334 6.79657 23.8334 7.73938 23.8334 9.625V10.25C23.8334 10.7214 23.8334 10.9571 23.6869 11.1036C23.5405 11.25 23.3048 11.25 22.8334 11.25H3.16669C2.69528 11.25 2.45958 11.25 2.31313 11.1036C2.16669 10.9571 2.16669 10.7214 2.16669 10.25V9.625Z"
                  fill="white"
                />
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M2.16669 20.75C2.16669 22.6356 2.16669 23.5784 2.75247 24.1642C3.33826 24.75 4.28107 24.75 6.16669 24.75H19.8334C21.719 24.75 22.6618 24.75 23.2476 24.1642C23.8334 23.5784 23.8334 22.6356 23.8334 20.75V14.5C23.8334 14.0286 23.8334 13.7929 23.6869 13.6464C23.5405 13.5 23.3048 13.5 22.8334 13.5H3.16669C2.69528 13.5 2.45958 13.5 2.31313 13.6464C2.16669 13.7929 2.16669 14.0286 2.16669 14.5V20.75ZM7.58335 16.75C7.58335 16.2786 7.58335 16.0429 7.7298 15.8964C7.87625 15.75 8.11195 15.75 8.58335 15.75H10.9167C11.3881 15.75 11.6238 15.75 11.7702 15.8964C11.9167 16.0429 11.9167 16.2786 11.9167 16.75V17C11.9167 17.4714 11.9167 17.7071 11.7702 17.8536C11.6238 18 11.3881 18 10.9167 18H8.58335C8.11195 18 7.87625 18 7.7298 17.8536C7.58335 17.7071 7.58335 17.4714 7.58335 17V16.75ZM7.7298 20.3964C7.58335 20.5429 7.58335 20.7786 7.58335 21.25V21.5C7.58335 21.9714 7.58335 22.2071 7.7298 22.3536C7.87625 22.5 8.11195 22.5 8.58335 22.5H10.9167C11.3881 22.5 11.6238 22.5 11.7702 22.3536C11.9167 22.2071 11.9167 21.9714 11.9167 21.5V21.25C11.9167 20.7786 11.9167 20.5429 11.7702 20.3964C11.6238 20.25 11.3881 20.25 10.9167 20.25H8.58335C8.11195 20.25 7.87625 20.25 7.7298 20.3964ZM14.0834 16.75C14.0834 16.2786 14.0834 16.0429 14.2298 15.8964C14.3762 15.75 14.612 15.75 15.0834 15.75H17.4167C17.8881 15.75 18.1238 15.75 18.2702 15.8964C18.4167 16.0429 18.4167 16.2786 18.4167 16.75V17C18.4167 17.4714 18.4167 17.7071 18.2702 17.8536C18.1238 18 17.8881 18 17.4167 18H15.0834C14.612 18 14.3762 18 14.2298 17.8536C14.0834 17.7071 14.0834 17.4714 14.0834 17V16.75ZM14.2298 20.3964C14.0834 20.5429 14.0834 20.7786 14.0834 21.25V21.5C14.0834 21.9714 14.0834 22.2071 14.2298 22.3536C14.3762 22.5 14.612 22.5 15.0834 22.5H17.4167C17.8881 22.5 18.1238 22.5 18.2702 22.3536C18.4167 22.2071 18.4167 21.9714 18.4167 21.5V21.25C18.4167 20.7786 18.4167 20.5429 18.2702 20.3964C18.1238 20.25 17.8881 20.25 17.4167 20.25H15.0834C14.612 20.25 14.3762 20.25 14.2298 20.3964Z"
                  fill="white"
                />
                <path
                  d="M7.58331 3.375L7.58331 6.75"
                  stroke="white"
                  stroke-width="2"
                  stroke-linecap="round"
                />
                <path
                  d="M18.4167 3.375L18.4167 6.75"
                  stroke="white"
                  stroke-width="2"
                  stroke-linecap="round"
                />
              </svg>

              <a>Calendar</a>
            </li>
            <li class="flex flex-col items-center">
              <svg
                width="18"
                height="24"
                viewBox="0 0 18 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M3 3V24L10.5 16.5L18 24V3H3ZM15 0H0V21L1.5 19.5V1.5H15V0Z"
                  fill="white"
                />
              </svg>

              <a>Bookmark</a>
            </li>
          </ul>
        </div>

        <!-- Logo -->
        <a class="btn btn-ghost text-xl font-inter text-dark-slate"
          >Pathfinder</a
        >

        <!-- Profile Button -->
        <button class="btn btn-ghost">
          <svg
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M15.6001 9.6C15.6001 11.5882 13.9884 13.2 12.0001 13.2C10.0119 13.2 8.40015 11.5882 8.40015 9.6C8.40015 7.61177 10.0119 6 12.0001 6C13.9884 6 15.6001 7.61177 15.6001 9.6ZM14.4001 9.6C14.4001 10.9255 13.3256 12 12.0001 12C10.6747 12 9.60015 10.9255 9.60015 9.6C9.60015 8.27452 10.6747 7.2 12.0001 7.2C13.3256 7.2 14.4001 8.27452 14.4001 9.6Z"
              fill="#44576D"
            />
            <path
              d="M12.0001 15C8.11552 15 4.80571 17.297 3.54492 20.5152C3.85206 20.8202 4.1756 21.1087 4.51412 21.3792C5.45296 18.4246 8.39818 16.2 12.0001 16.2C15.6021 16.2 18.5473 18.4246 19.4862 21.3792C19.8247 21.1087 20.1482 20.8202 20.4554 20.5152C19.1946 17.2971 15.8848 15 12.0001 15Z"
              fill="#44576D"
            />
          </svg>
        </button>
      </div>

      <!-- Large Screen: Logo, Menu, Search -->
      <div class="hidden lg:flex w-full items-center justify-between">
        <!-- Left Side: Logo + Menu -->
        <div class="flex items-center gap-6">
          <!-- Logo -->
          <a class="btn btn-ghost text-xl font-inter text-dark-slate"
            >Pathfinder</a
          >

          <!-- Horizontal Menu -->
          <ul class="menu menu-horizontal px-1">
            <li><a>Organization</a></li>
            <li><a>Training</a></li>
            <li><a>Career</a></li>
            <li><a>Calendar</a></li>
            <li><a>Bookmark</a></li>
          </ul>
        </div>

        <!-- Center: Search bar -->
        <div class="flex-grow flex justify-center">
          <label
            class="input w-full max-w-md flex items-center gap-2 shadow-sm rounded px-3 py-2"
          >
            <svg
              class="h-5 w-5 text-gray-500"
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
              class="flex-grow bg-transparent outline-none"
            />
          </label>
        </div>

        <!-- Right: Profile -->
        <div>
          <button class="btn btn-ghost">
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M15.6001 9.6C15.6001 11.5882 13.9884 13.2 12.0001 13.2C10.0119 13.2 8.40015 11.5882 8.40015 9.6C8.40015 7.61177 10.0119 6 12.0001 6C13.9884 6 15.6001 7.61177 15.6001 9.6ZM14.4001 9.6C14.4001 10.9255 13.3256 12 12.0001 12C10.6747 12 9.60015 10.9255 9.60015 9.6C9.60015 8.27452 10.6747 7.2 12.0001 7.2C13.3256 7.2 14.4001 8.27452 14.4001 9.6Z"
                fill="#44576D"
              />
              <path
                d="M12.0001 15C8.11552 15 4.80571 17.297 3.54492 20.5152C3.85206 20.8202 4.1756 21.1087 4.51412 21.3792C5.45296 18.4246 8.39818 16.2 12.0001 16.2C15.6021 16.2 18.5473 18.4246 19.4862 21.3792C19.8247 21.1087 20.1482 20.8202 20.4554 20.5152C19.1946 17.2971 15.8848 15 12.0001 15Z"
                fill="#44576D"
              />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile/Tablet Search bar (below navbar) -->
    <div class="lg:hidden flex justify-center px-0 mt-2">
      <label
        class="input w-full max-w-md flex items-center gap-2 shadow-sm rounded px-3 py-2"
      >
        <svg
          class="h-5 w-5 text-gray-500"
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
          class="flex-grow bg-transparent outline-none"
        />
      </label>
    </div>
  </div>
</template>
