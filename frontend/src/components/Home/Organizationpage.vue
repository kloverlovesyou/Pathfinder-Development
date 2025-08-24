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
          d="M30.1176 29.0625V27.125H28.2353V15.5H30.1176V13.5625H24.4706V15.5H26.3529V27.125H20.7059V15.5H22.5882V13.5625H16.9412V15.5H18.8235V27.125H13.1765V15.5H15.0588V13.5625H9.41177V15.5H11.2941V27.125H5.64706V15.5H7.52941V13.5625H1.88235V15.5H3.76471V27.125H1.88235V29.0625H0V31H32V29.0625H30.1176Z"
          fill="#44576D"
        />
        <path
          d="M15.0588 0H16.9412L32 9.6875V11.625H0V9.6875L15.0588 0Z"
          fill="#44576D"
        />
      </svg>

      <h2 class="text-2xl font-bold -m-2">Organizations</h2>
    </div>
    <div class="flex flex-col gap-8">
      <div class="flex flex-wrap -mx-2">
        <div class="sm:w-1/2 p-2">
          <div
            class="flex flex-col items-center p-4 bg-white rounded-lg shadow-md"
          >
            <div class="profile-picture">
              <img
                v-if="profileImageUrl"
                :src="profileImageUrl"
                alt="User profile picture"
                class="rounded-full w-20 h-20 object-cover"
              />

              <svg
                v-else
                class="rounded-full w-20 h-20 text-gray-400"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M9 2C6.38 2 4.25 4.13 4.25 6.75C4.25 9.32 6.26 11.4 8.88 11.49C8.96 11.48 9.04 11.48 9.1 11.49C9.12 11.49 9.13 11.49 9.15 11.49C9.16 11.49 9.16 11.49 9.17 11.49C11.73 11.4 13.74 9.32 13.75 6.75C13.75 4.13 11.62 2 9 2Z"
                  fill="#292D32"
                />
                <path
                  d="M14.08 14.15C11.29 12.29 6.73996 12.29 3.92996 14.15C2.65996 15 1.95996 16.15 1.95996 17.38C1.95996 18.61 2.65996 19.75 3.91996 20.59C5.31996 21.53 7.15996 22 8.99996 22C10.84 22 12.68 21.53 14.08 20.59C15.34 19.74 16.04 18.6 16.04 17.36C16.03 16.13 15.34 14.99 14.08 14.15Z"
                  fill="#292D32"
                />
                <path
                  d="M19.9901 7.33998C20.1501 9.27998 18.7701 10.98 16.8601 11.21C16.8501 11.21 16.8501 11.21 16.8401 11.21H16.8101C16.7501 11.21 16.6901 11.21 16.6401 11.23C15.6701 11.28 14.7801 10.97 14.1101 10.4C15.1401 9.47998 15.7301 8.09998 15.6101 6.59998C15.5401 5.78998 15.2601 5.04998 14.8401 4.41998C15.2201 4.22998 15.6601 4.10998 16.1101 4.06998C18.0701 3.89998 19.8201 5.35998 19.9901 7.33998Z"
                  fill="#292D32"
                />
                <path
                  d="M21.99 16.59C21.91 17.56 21.29 18.4 20.25 18.97C19.25 19.52 17.99 19.78 16.74 19.75C17.46 19.1 17.88 18.29 17.96 17.43C18.06 16.19 17.47 15 16.29 14.05C15.62 13.52 14.84 13.1 13.99 12.79C16.2 12.15 18.98 12.58 20.69 13.96C21.61 14.7 22.08 15.63 21.99 16.59Z"
                  fill="#292D32"
                />
              </svg>
            </div>
            <span class="text-lg font-semibold text-gray-800">Org. Name A</span>
          </div>
        </div>

        <div class="sm:w-1/2 p-2">
          <div
            class="flex flex-col items-center p-4 bg-white rounded-lg shadow-md"
          >
            <div class="profile-picture">
              <img
                v-if="profileImageUrl"
                :src="profileImageUrl"
                alt="User profile picture"
                class="rounded-full w-20 h-20 object-cover"
              />

              <svg
                v-else
                class="rounded-full w-20 h-20 text-gray-400"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M9 2C6.38 2 4.25 4.13 4.25 6.75C4.25 9.32 6.26 11.4 8.88 11.49C8.96 11.48 9.04 11.48 9.1 11.49C9.12 11.49 9.13 11.49 9.15 11.49C9.16 11.49 9.16 11.49 9.17 11.49C11.73 11.4 13.74 9.32 13.75 6.75C13.75 4.13 11.62 2 9 2Z"
                  fill="#292D32"
                />
                <path
                  d="M14.08 14.15C11.29 12.29 6.73996 12.29 3.92996 14.15C2.65996 15 1.95996 16.15 1.95996 17.38C1.95996 18.61 2.65996 19.75 3.91996 20.59C5.31996 21.53 7.15996 22 8.99996 22C10.84 22 12.68 21.53 14.08 20.59C15.34 19.74 16.04 18.6 16.04 17.36C16.03 16.13 15.34 14.99 14.08 14.15Z"
                  fill="#292D32"
                />
                <path
                  d="M19.9901 7.33998C20.1501 9.27998 18.7701 10.98 16.8601 11.21C16.8501 11.21 16.8501 11.21 16.8401 11.21H16.8101C16.7501 11.21 16.6901 11.21 16.6401 11.23C15.6701 11.28 14.7801 10.97 14.1101 10.4C15.1401 9.47998 15.7301 8.09998 15.6101 6.59998C15.5401 5.78998 15.2601 5.04998 14.8401 4.41998C15.2201 4.22998 15.6601 4.10998 16.1101 4.06998C18.0701 3.89998 19.8201 5.35998 19.9901 7.33998Z"
                  fill="#292D32"
                />
                <path
                  d="M21.99 16.59C21.91 17.56 21.29 18.4 20.25 18.97C19.25 19.52 17.99 19.78 16.74 19.75C17.46 19.1 17.88 18.29 17.96 17.43C18.06 16.19 17.47 15 16.29 14.05C15.62 13.52 14.84 13.1 13.99 12.79C16.2 12.15 18.98 12.58 20.69 13.96C21.61 14.7 22.08 15.63 21.99 16.59Z"
                  fill="#292D32"
                />
              </svg>
            </div>
            <span class="text-lg font-semibold text-gray-800">Org. Name B</span>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap -mx-2">
        <div class="sm:w-1/2 p-2">
          <div
            class="flex flex-col items-center p-4 bg-white rounded-lg shadow-md"
          >
            <div class="profile-picture">
              <img
                v-if="profileImageUrl"
                :src="profileImageUrl"
                alt="User profile picture"
                class="rounded-full w-20 h-20 object-cover"
              />

              <svg
                v-else
                class="rounded-full w-20 h-20 text-gray-400"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M9 2C6.38 2 4.25 4.13 4.25 6.75C4.25 9.32 6.26 11.4 8.88 11.49C8.96 11.48 9.04 11.48 9.1 11.49C9.12 11.49 9.13 11.49 9.15 11.49C9.16 11.49 9.16 11.49 9.17 11.49C11.73 11.4 13.74 9.32 13.75 6.75C13.75 4.13 11.62 2 9 2Z"
                  fill="#292D32"
                />
                <path
                  d="M14.08 14.15C11.29 12.29 6.73996 12.29 3.92996 14.15C2.65996 15 1.95996 16.15 1.95996 17.38C1.95996 18.61 2.65996 19.75 3.91996 20.59C5.31996 21.53 7.15996 22 8.99996 22C10.84 22 12.68 21.53 14.08 20.59C15.34 19.74 16.04 18.6 16.04 17.36C16.03 16.13 15.34 14.99 14.08 14.15Z"
                  fill="#292D32"
                />
                <path
                  d="M19.9901 7.33998C20.1501 9.27998 18.7701 10.98 16.8601 11.21C16.8501 11.21 16.8501 11.21 16.8401 11.21H16.8101C16.7501 11.21 16.6901 11.21 16.6401 11.23C15.6701 11.28 14.7801 10.97 14.1101 10.4C15.1401 9.47998 15.7301 8.09998 15.6101 6.59998C15.5401 5.78998 15.2601 5.04998 14.8401 4.41998C15.2201 4.22998 15.6601 4.10998 16.1101 4.06998C18.0701 3.89998 19.8201 5.35998 19.9901 7.33998Z"
                  fill="#292D32"
                />
                <path
                  d="M21.99 16.59C21.91 17.56 21.29 18.4 20.25 18.97C19.25 19.52 17.99 19.78 16.74 19.75C17.46 19.1 17.88 18.29 17.96 17.43C18.06 16.19 17.47 15 16.29 14.05C15.62 13.52 14.84 13.1 13.99 12.79C16.2 12.15 18.98 12.58 20.69 13.96C21.61 14.7 22.08 15.63 21.99 16.59Z"
                  fill="#292D32"
                />
              </svg>
            </div>
            <span class="text-lg font-semibold text-gray-800">Org. Name C</span>
          </div>
        </div>

        <div class="sm:w-1/2 p-2">
          <div
            class="flex flex-col items-center p-4 bg-white rounded-lg shadow-md"
          >
            <div class="profile-picture">
              <img
                v-if="profileImageUrl"
                :src="profileImageUrl"
                alt="User profile picture"
                class="rounded-full w-20 h-20 object-cover"
              />

              <svg
                v-else
                class="rounded-full w-20 h-20 text-gray-400"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M9 2C6.38 2 4.25 4.13 4.25 6.75C4.25 9.32 6.26 11.4 8.88 11.49C8.96 11.48 9.04 11.48 9.1 11.49C9.12 11.49 9.13 11.49 9.15 11.49C9.16 11.49 9.16 11.49 9.17 11.49C11.73 11.4 13.74 9.32 13.75 6.75C13.75 4.13 11.62 2 9 2Z"
                  fill="#292D32"
                />
                <path
                  d="M14.08 14.15C11.29 12.29 6.73996 12.29 3.92996 14.15C2.65996 15 1.95996 16.15 1.95996 17.38C1.95996 18.61 2.65996 19.75 3.91996 20.59C5.31996 21.53 7.15996 22 8.99996 22C10.84 22 12.68 21.53 14.08 20.59C15.34 19.74 16.04 18.6 16.04 17.36C16.03 16.13 15.34 14.99 14.08 14.15Z"
                  fill="#292D32"
                />
                <path
                  d="M19.9901 7.33998C20.1501 9.27998 18.7701 10.98 16.8601 11.21C16.8501 11.21 16.8501 11.21 16.8401 11.21H16.8101C16.7501 11.21 16.6901 11.21 16.6401 11.23C15.6701 11.28 14.7801 10.97 14.1101 10.4C15.1401 9.47998 15.7301 8.09998 15.6101 6.59998C15.5401 5.78998 15.2601 5.04998 14.8401 4.41998C15.2201 4.22998 15.6601 4.10998 16.1101 4.06998C18.0701 3.89998 19.8201 5.35998 19.9901 7.33998Z"
                  fill="#292D32"
                />
                <path
                  d="M21.99 16.59C21.91 17.56 21.29 18.4 20.25 18.97C19.25 19.52 17.99 19.78 16.74 19.75C17.46 19.1 17.88 18.29 17.96 17.43C18.06 16.19 17.47 15 16.29 14.05C15.62 13.52 14.84 13.1 13.99 12.79C16.2 12.15 18.98 12.58 20.69 13.96C21.61 14.7 22.08 15.63 21.99 16.59Z"
                  fill="#292D32"
                />
              </svg>
            </div>
            <span class="text-lg font-semibold text-gray-800">Org. Name D</span>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
