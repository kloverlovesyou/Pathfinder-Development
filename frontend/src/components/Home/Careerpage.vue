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
        width="38"
        height="38"
        viewBox="0 0 38 38"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M31.8973 30.1637C32.3487 30.6555 33.1361 30.6831 33.5858 30.1898C35.369 28.2338 36.6727 25.9088 37.384 23.3834C38.1768 20.5691 38.2022 17.6188 37.4727 14.8092C37.3062 14.168 36.5881 13.8437 35.9533 14.0858L23.6016 18.796C22.8331 19.0891 22.6003 20.0341 23.1491 20.6321L31.8973 30.1637Z"
          fill="#44576D"
        />
        <path
          d="M35.6224 10.4086C35.9708 10.989 35.6764 11.7176 35.0316 11.9635L22.8098 16.6242C22.0306 16.9213 21.1875 16.3647 21.1875 15.5532V3.03765C21.1875 2.37259 21.7682 1.84247 22.4482 1.93147C25.2712 2.30096 27.9742 3.30597 30.3271 4.87732C32.5081 6.33381 34.3122 8.22602 35.6224 10.4086Z"
          fill="#44576D"
        />
        <path
          d="M18.8125 1.11928C18.8125 0.45718 18.2369 -0.0715826 17.5587 0.00793401C15.1204 0.293833 12.751 1.00646 10.5721 2.11679C7.67144 3.59493 5.19791 5.73225 3.37062 8.33944C1.54334 10.9466 0.418692 13.9432 0.0963109 17.0637C-0.226071 20.1843 0.263762 23.3325 1.52242 26.2294C2.78108 29.1264 4.76971 31.6827 7.31216 33.6719C9.85462 35.6611 12.8724 37.0219 16.0983 37.6336C19.3241 38.2454 22.6584 38.0892 25.8058 37.179C27.7304 36.6224 29.5488 35.7943 31.2029 34.728C31.7786 34.3569 31.8543 33.5803 31.3948 33.0796L19.1578 19.7467C18.9354 19.5043 18.8125 19.1913 18.8125 18.8669V1.11928Z"
          fill="#44576D"
        />
        <path
          d="M33.3959 31.7965C33.4585 31.8647 33.5208 31.8229 33.4537 31.7589L32.7151 31.0547L33.3959 31.7965Z"
          fill="#44576D"
        />
      </svg>

      <h2 class="text-2xl font-bold -m-2">Career</h2>
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
              <li><a>Bookmark</a></li>
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
              <li><a>Bookmark</a></li>
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
              <li><a>Bookmark</a></li>
              <li><a>Apply</a></li>
            </ul>
          </div>
        </div>
        <p class="text-gray-600 text-sm">October 24, 2025</p>
      </div>
    </div>
  </main>
</template>
