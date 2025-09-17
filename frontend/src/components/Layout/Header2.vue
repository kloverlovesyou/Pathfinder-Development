<script setup>
import { useAuthStore } from "@/stores/auth";
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useRouter } from "vue-router";
const route = useRoute();
const router = useRouter();
const userName = ref("");
const auth = useAuthStore();

function handleLogout() {
  auth.logout();
  route.push("/loginform");
}

onMounted(() => {
  const savedUser = localStorage.getItem("user");
  if (savedUser) {
    const user = JSON.parse(savedUser);
    if (user.firstName && user.lastName) {
      userName.value = `${user.firstName} ${user.lastName}`;
    } else {
      userName.value = "Guest";
    }
  } else {
    userName.value = "Guest";
  }
});
</script>

<template>
  <div class="w-full h-full font-poppins">
    <!-- Navbar -->
    <div
      class="navbar flex-col lg:flex-row items-center justify-between gap-2 p-4"
    >
      <!-- Mobile View: Logo, Menu & Logout in One Row -->
      <div class="w-full flex items-center justify-between lg:hidden">
        <!-- Mobile Menu Button -->
        <div class="dropdown w-10">
          <button tabindex="0" class="btn btn-ghost">
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
              <button
                class="flex flex-col items-center w-full"
                @click="router.push('/organizationpage')"
              >
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
              </button>
            </li>
            <li class="flex flex-col items-center">
              <button
                class="flex flex-col items-center w-full"
                @click="router.push('/trainingpage')"
              >
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
              </button>
            </li>
            <li class="flex flex-col items-center">
              <button
                class="flex flex-col items-center w-full"
                @click="router.push('/careerpage')"
              >
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
              </button>
            </li>
            <li class="flex flex-col items-center">
              <button
                class="flex flex-col items-center w-full"
                @click="router.push('/calendarpage')"
              >
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
              </button>
            </li>
            <li class="flex flex-col items-center">
              <button
                class="flex flex-col items-center w-full"
                @click="router.push('/bookmarkpage')"
              >
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
              </button>
            </li>
          </ul>
        </div>

        <!-- Logo -->
        <a
          class="text-xl font-poppins font-bold text-dark-slate"
          @click="router.push('/homepage')"
          >Pathfinder</a
        >

        <!-- Profile Button sm-->
        <div class="w-10">
          <button
            class="btn btn-ghost"
            :class="route.path === '/profilepage' ? 'text-white' : ''"
            @click="router.push('/profilepage')"
            v-if="route.path !== '/profilepage'"
          >
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

      <!-- Large Screen: Logo, Menu, Search -->
      <div class="hidden lg:flex w-full items-center justify-between">
        <!-- Left Side: Drawer + Logo -->
        <div class="flex items-center gap-2">
          <!-- Drawer -->
          <div class="drawer">
            <!-- Drawer toggle -->
            <input id="my-drawer" type="checkbox" class="drawer-toggle" />

            <!-- Page content -->
            <div class="drawer-content">
              <!-- Button to open sidebar -->
              <label for="my-drawer" class="btn btn-ghost hover:bg-transparent">
                <svg
                  width="25"
                  height="24"
                  viewBox="0 0 45 44"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M15.3333 11H39.1667M15.3333 22H39.1667M15.3333 33H39.1667M6.16666 11H6.18499M6.16666 22H6.18499M6.16666 33H6.18499"
                    stroke="#44576D"
                    stroke-width="4"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </label>
            </div>

            <!-- Drawer sidebar -->
            <div class="drawer-side z-50">
              <label
                for="my-drawer"
                aria-label="close sidebar"
                class="drawer-overlay"
              ></label>

              <ul class="menu bg-dark-slate text-base-content min-h-full w-80">
                <div class="flex items-center gap-2">
                  <label
                    for="my-drawer"
                    class="btn btn-ghost hover:bg-transparent"
                  >
                    <svg
                      width="25"
                      height="24"
                      viewBox="0 0 45 44"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M15.5 11H39.3333M15.5 22H39.3333M15.5 33H39.3333M6.33331 11H6.35165M6.33331 22H6.35165M6.33331 33H6.35165"
                        stroke="white"
                        stroke-width="4"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                  </label>
                  <span class="font-poppins font-bold text-white text-xl">
                    Pathfinder</span
                  >
                </div>
                <button @click="router.push('/profilepage')">
                  <div class="flex flex-col items-center gap-2 mb-6 pt-4">
                    <div class="avatar">
                      <div class="w-16 rounded-full">
                        <img
                          src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp"
                          alt="User Avatar"
                        />
                      </div>
                    </div>
                    <span class="text-xl font-bold text-white">{{
                      userName
                    }}</span>
                  </div>
                </button>
                <li>
                  <button
                    class="text-white py-2 text-lg hover:bg-dark-slate flex items-center justify-start gap-2"
                    @click="router.push('/trainingpage')"
                  >
                    <svg
                      class="size-6 flex-shrink-0"
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
                    <span>Training</span>
                  </button>
                </li>
                <li>
                  <button
                    class="text-white py-2 text-lg hover:bg-dark-slate flex items-center justify-start gap-2"
                    @click="router.push('/careerpage')"
                  >
                    <svg
                      class="size-6 flex-shrink-0"
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
                    <span>Career</span>
                  </button>
                </li>
                <li>
                  <button
                    class="text-white py-2 text-lg hover:bg-dark-slate flex items-center justify-start gap-2"
                    @click="router.push('/organizationpage')"
                  >
                    <svg
                      class="size-6 flex-shrink-0"
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
                    <span>Organizations</span>
                  </button>
                </li>
                <li>
                  <button
                    class="text-white py-2 text-lg hover:bg-dark-slate flex items-center justify-start gap-2"
                    @click="router.push('/resumepage')"
                  >
                    <svg
                      class="size-6 flex-shrink-0"
                      viewBox="0 0 34 34"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M19.8333 3.21521V9.06681C19.8333 9.86022 19.8333 10.2569 19.9877 10.56C20.1235 10.8265 20.3402 11.0432 20.6068 11.1791C20.9098 11.3335 21.3066 11.3335 22.1 11.3335H27.9516M22.6666 18.4167H11.3333M22.6666 24.0834H11.3333M14.1666 12.75H11.3333M19.8333 2.83337H12.4666C10.0864 2.83337 8.89629 2.83337 7.98717 3.2966C7.18748 3.70406 6.53731 4.35423 6.12985 5.15391C5.66663 6.06304 5.66663 7.25315 5.66663 9.63337V24.3667C5.66663 26.7469 5.66663 27.937 6.12985 28.8462C6.53731 29.6459 7.18748 30.296 7.98717 30.7035C8.89629 31.1667 10.0864 31.1667 12.4666 31.1667H21.5333C23.9135 31.1667 25.1036 31.1667 26.0128 30.7035C26.8124 30.296 27.4626 29.6459 27.8701 28.8462C28.3333 27.937 28.3333 26.7469 28.3333 24.3667V11.3334L19.8333 2.83337Z"
                        stroke="white"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                    <span>Resume</span>
                  </button>
                </li>
                <li>
                  <button
                    class="text-white py-2 text-lg hover:bg-dark-slate flex items-center justify-start gap-2"
                    @click="router.push('/certificatespage')"
                  >
                    <svg
                      class="size-6 flex-shrink-0"
                      viewBox="0 0 35 35"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M9.47917 29.1666H7.29167C5.68084 29.1666 4.375 27.8608 4.375 26.25V5.83329C4.375 4.22246 5.68084 2.91663 7.29167 2.91663H27.7083C29.3192 2.91663 30.625 4.22246 30.625 5.83329V26.25C30.625 27.8608 29.3192 29.1666 27.7083 29.1666H25.5208M17.5 27.7083C19.9162 27.7083 21.875 25.7495 21.875 23.3333C21.875 20.917 19.9162 18.9583 17.5 18.9583C15.0838 18.9583 13.125 20.917 13.125 23.3333C13.125 25.7495 15.0838 27.7083 17.5 27.7083ZM17.5 27.7083L17.5313 27.708L12.8751 32.3641L8.75036 28.2393L13.1537 23.836M17.5 27.7083L22.1562 32.3641L26.281 28.2393L21.8777 23.836M13.125 8.74996H21.875M10.2083 13.8541H24.7917"
                        stroke="white"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                    <span>Certificates</span>
                  </button>
                </li>

                <li>
                  <button
                    class="text-white py-2 text-lg hover:bg-dark-slate flex items-center justify-start gap-2"
                    @click="router.push('/calendarpage')"
                  >
                    <svg
                      class="size-6 flex-shrink-0"
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
                    <span>Calendar</span>
                  </button>
                </li>
                <li>
                  <button
                    class="text-white py-2 text-lg hover:bg-dark-slate flex items-center justify-start gap-2"
                    @click="router.push('/bookmarkpage')"
                  >
                    <svg
                      class="size-6 flex-shrink-0"
                      viewBox="0 0 18 24"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M3 3V24L10.5 16.5L18 24V3H3ZM15 0H0V21L1.5 19.5V1.5H15V0Z"
                        fill="white"
                      />
                    </svg>
                    <span>Bookmark</span>
                  </button>
                </li>
                <li>
                  <button
                    class="text-white py-2 text-lg hover:bg-dark-slate flex items-center justify-start gap-2"
                    @click="router.push('/updatedeletepage')"
                  >
                    <svg
                      class="size-6 flex-shrink-0"
                      viewBox="0 0 24 24"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M20.1 9.21994C18.29 9.21994 17.55 7.93994 18.45 6.36994C18.97 5.45994 18.66 4.29994 17.75 3.77994L16.02 2.78994C15.23 2.31994 14.21 2.59994 13.74 3.38994L13.63 3.57994C12.73 5.14994 11.25 5.14994 10.34 3.57994L10.23 3.38994C9.78 2.59994 8.76 2.31994 7.97 2.78994L6.24 3.77994C5.33 4.29994 5.02 5.46994 5.54 6.37994C6.45 7.93994 5.71 9.21994 3.9 9.21994C2.86 9.21994 2 10.0699 2 11.1199V12.8799C2 13.9199 2.85 14.7799 3.9 14.7799C5.71 14.7799 6.45 16.0599 5.54 17.6299C5.02 18.5399 5.33 19.6999 6.24 20.2199L7.97 21.2099C8.76 21.6799 9.78 21.3999 10.25 20.6099L10.36 20.4199C11.26 18.8499 12.74 18.8499 13.65 20.4199L13.76 20.6099C14.23 21.3999 15.25 21.6799 16.04 21.2099L17.77 20.2199C18.68 19.6999 18.99 18.5299 18.47 17.6299C17.56 16.0599 18.3 14.7799 20.11 14.7799C21.15 14.7799 22.01 13.9299 22.01 12.8799V11.1199C22 10.0799 21.15 9.21994 20.1 9.21994ZM12 15.2499C10.21 15.2499 8.75 13.7899 8.75 11.9999C8.75 10.2099 10.21 8.74994 12 8.74994C13.79 8.74994 15.25 10.2099 15.25 11.9999C15.25 13.7899 13.79 15.2499 12 15.2499Z"
                        fill="white"
                      />
                    </svg>

                    <span>Account Setting</span>
                  </button>
                </li>
                <li>
                  <button
                    class="text-white py-2 text-lg hover:bg-dark-slate flex items-center justify-start gap-2"
                  >
                    <svg
                      class="size-6 flex-shrink-0"
                      viewBox="0 0 24 24"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M15.24 22.27H15.11C10.67 22.27 8.53002 20.52 8.16002 16.6C8.12002 16.19 8.42002 15.82 8.84002 15.78C9.24002 15.74 9.62002 16.05 9.66002 16.46C9.95002 19.6 11.43 20.77 15.12 20.77H15.25C19.32 20.77 20.76 19.33 20.76 15.26V8.73998C20.76 4.66998 19.32 3.22998 15.25 3.22998H15.12C11.41 3.22998 9.93002 4.41998 9.66002 7.61998C9.61002 8.02998 9.26002 8.33998 8.84002 8.29998C8.42002 8.26998 8.12001 7.89998 8.15001 7.48998C8.49001 3.50998 10.64 1.72998 15.11 1.72998H15.24C20.15 1.72998 22.25 3.82998 22.25 8.73998V15.26C22.25 20.17 20.15 22.27 15.24 22.27Z"
                        fill="white"
                      />
                      <path
                        d="M15.0001 12.75H3.62012C3.21012 12.75 2.87012 12.41 2.87012 12C2.87012 11.59 3.21012 11.25 3.62012 11.25H15.0001C15.4101 11.25 15.7501 11.59 15.7501 12C15.7501 12.41 15.4101 12.75 15.0001 12.75Z"
                        fill="white"
                      />
                      <path
                        d="M5.84994 16.1C5.65994 16.1 5.46994 16.03 5.31994 15.88L1.96994 12.53C1.67994 12.24 1.67994 11.76 1.96994 11.47L5.31994 8.12003C5.60994 7.83003 6.08994 7.83003 6.37994 8.12003C6.66994 8.41003 6.66994 8.89003 6.37994 9.18003L3.55994 12L6.37994 14.82C6.66994 15.11 6.66994 15.59 6.37994 15.88C6.23994 16.03 6.03994 16.1 5.84994 16.1Z"
                        fill="white"
                      />
                    </svg>

                    <span>Logout</span>
                  </button>
                </li>
              </ul>
            </div>
          </div>

          <!-- Logo -->
          <button
            class="text-xl font-poppins font-bold text-dark-slate"
            @click="router.push('/homepage')"
          >
            Pathfinder
          </button>
        </div>

        <!-- Center: Search bar  lg-->
        <div class="flex-grow flex justify-center">
          <label
            class="bg-blue-gray input w-full max-w-md flex items-center gap-2 border-none rounded-full px-3 py-2"
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
              class="flex-grow bg-transparent outline-none font-poppins"
            />
          </label>
        </div>
      </div>
    </div>

    <!-- Mobile/Tablet Search bar (below navbar) -->
    <div
      class="lg:hidden flex justify-center px-0 mb-2"
      v-if="
        route.path !== '/profilepage' &&
        route.path !== '/certificatespage' &&
        route.path !== '/updatedeletepage' &&
        route.path !== '/resumepage'
      "
    >
      <label
        class="bg-blue-gray input w-full max-w-md flex items-center gap-2 border-none rounded-full px-3"
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
          class="flex-grow bg-transparent outline-none font-poppins"
        />
      </label>
    </div>

    <div
      v-if="
        route.path !== '/profilepage' &&
        route.path !== '/certificatespage' &&
        route.path !== '/updatedeletepage' &&
        route.path !== '/resumepage'
      "
    >
      <div class="p-8 bg-blue-gray min-h-screen font-poppins" v-if="!isMobile">
        <div class="flex gap-8 items-start">
          <main class="flex-grow bg-white p-6 rounded-lg shadow-md">
            <router-view></router-view>
          </main>

          <aside
            class="w-1/5 bg-white p-4 rounded-lg shadow-md hidden lg:block"
            v-if="
              route.path !== '/profilepage' &&
              route.path !== '/certificatespage' &&
              route.path !== '/resumepage' &&
              route.path !== '/bookmarkpage'
            "
          >
            <h2
              class="text-xl font-bold mb-4"
              v-if="
                route.path !== '/calendarpage' && route.path !== '/bookmarkpage'
              "
            >
              Calendar
            </h2>
            <div
              class="bg-blue-gray p-4 rounded-md cally bg-base-100 border border-base-300 shadow-lg rounded-box flex justify-center"
              v-if="
                route.path !== '/calendarpage' &&
                route.path !== '/profilepage' &&
                route.path !== '/certificatespage' &&
                route.path !== '/updatedeletepage' &&
                route.path !== '/resumepage' &&
                route.path !== '/bookmarkpage'
              "
            >
              <calendar-date>
                <svg
                  aria-label="Previous"
                  class="fill-current size-4"
                  slot="previous"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                >
                  <path
                    fill="currentColor"
                    d="M15.75 19.5 8.25 12l7.5-7.5"
                  ></path>
                </svg>
                <svg
                  aria-label="Next"
                  class="fill-current size-4"
                  slot="next"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                >
                  <path
                    fill="currentColor"
                    d="m8.25 4.5 7.5 7.5-7.5 7.5"
                  ></path>
                </svg>
                <calendar-month></calendar-month>
              </calendar-date>
            </div>
            <div class="mt-8" v-if="route.path !== '/bookmarkpage'">
              <h2 class="text-xl font-bold mb-4">Upcoming</h2>
              <div class="space-y-4">
                <div class="p-4 bg-gray-100 rounded-lg">
                  <h3 class="font-semibold hover:text-dark-slate">
                    Professional development in emerging technologies and
                    cognitive enhancement
                  </h3>
                  <p class="text-gray-600 text-sm">Tomorrow, 10:00 AM</p>
                </div>
                <div class="p-4 bg-gray-100 rounded-lg">
                  <h3 class="font-semibold hover:text-dark-slate">
                    Mind Over Machine: Navigating AI in Everyday Life
                  </h3>
                  <p class="text-gray-600 text-sm">Dec 15</p>
                </div>
                <div class="p-4 bg-gray-100 rounded-lg">
                  <h3 class="font-semibold hover:text-dark-slate">
                    Business Conference
                  </h3>
                  <p class="text-gray-600 text-sm">Dec 18, 2:00 PM</p>
                </div>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </div>
  </div>
</template>
