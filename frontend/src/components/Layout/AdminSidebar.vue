<template>
  <div class="flex relative font-poppins">
    <!-- Sidebar -->
    <div
      ref="sidebar"
      :class="[
        'h-screen bg-dark-slate text-white transition-all duration-300 flex flex-col',
        isExpanded ? 'w-75 px-4' : 'w-16 items-center',
      ]"
    >
      <!-- Menu Button -->
      <div class="flex items-center">
        <button
          class="p-2 mt-4 mb-2 hover:bg-slate-700 rounded-lg"
          @click.stop="toggleSidebar"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
          </svg>
        </button>
      </div>

      <!-- Nav Items -->
      <aside class="flex flex-col h-screen">
        <!-- TOP + MENU -->
        <ul class="flex-1 flex flex-col p-2 w-full">
          <li>
            <button @click="$router.push({ name: 'Profile' })" class="w-full">
              <div
                class="flex flex-col items-center justify-center gap-2 mb-6 pt-4 mx-auto"
              >
                <div class="avatar">
                  <div
                    :class="[
                      isExpanded ? 'w-16' : 'w-10',
                      'rounded-full',
                      'transition-all',
                      'duration-300',
                    ]"
                  >
                    <img
                      src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp"
                      alt="User Avatar"
                    />
                  </div>
                </div>
                <span
                  v-if="isExpanded"
                  class="text-xl font-bold text-white transition-opacity duration-300"
                >
                  {{ userName }}
                </span>
              </div>
            </button>
          </li>
          <li>
            <button
              class="text-white mt-3 gap-3 p-3 py-2 hover:bg-slate-700 rounded-lg w-full flex items-center justify-start"
              @click="$router.push({ name: 'AdminHomepage' })"
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
              <span v-if="isExpanded">Verify Account</span>
            </button>
          </li>
        </ul>

        <!-- LOGOUT -->
        <div class="p-2">
          <ul>
            <li>
              <button
                class="text-white gap-3 p-3 py-2 hover:bg-slate-700 rounded-lg w-full flex items-center justify-start"
                @click="$router.push({ name: 'UpdateDeletepage' })"
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
                <span v-if="isExpanded">Account Setting</span>
              </button>
            </li>
            <li>
              <button
                class="text-white gap-3 p-3 py-2 hover:bg-slate-700 rounded-lg w-full flex items-center justify-start"
                @click="logout"
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
                <span v-if="isExpanded">Logout</span>
              </button>
            </li>
          </ul>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRoute } from "vue-router";
import { useRouter } from "vue-router";
const route = useRoute();
const router = useRouter();
const userName = ref("");
const auth = useAuthStore();

const logout = () => {
  // Remove user data
  localStorage.removeItem("user");
  localStorage.removeItem("token"); // if you store an auth token
  // Redirect to login page
  router.push({ name: "Login" });
};

const isExpanded = ref(false);
const sidebar = ref(null);

const toggleSidebar = () => {
  isExpanded.value = !isExpanded.value;
};

const handleClickOutside = (event) => {
  if (
    isExpanded.value &&
    sidebar.value &&
    !sidebar.value.contains(event.target)
  ) {
    isExpanded.value = false;
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
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

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});
</script>

<style></style>
