<script setup>
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";

const auth = useAuthStore();
const router = useRouter();

function handleLogout() {
  auth.logout();
  router.push("/loginform");
}
</script>

<template>
  <div class="navbar bg-base-100 shadow-sm">
    <div class="navbar-start">
      <div class="dropdown" v-if="auth.isLoggedIn">
        <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
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
        </div>
        <ul
          tabindex="0"
          class="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow"
        >
          <li><a>Item 1</a></li>
          <li>
            <a>Parent</a>
            <ul class="p-2">
              <li><a>Submenu 1</a></li>
              <li><a>Submenu 2</a></li>
            </ul>
          </li>
          <li><a>Item 3</a></li>
        </ul>
      </div>
      <div
        class="absolute left-1/2 transform -translate-x-1/2 lg:static lg:translate-x-0 lg:left-auto navbar-center"
      >
        <a
          class="btn btn-ghost text-xl font-inter text-semibold text-dark-slate"
          >Pathfinder</a
        >
      </div>
    </div>

    <div class="navbar-center hidden lg:flex" v-if="auth.isLoggedIn">
      <ul class="menu menu-horizontal px-1">
        <li><a>Item 1</a></li>
        <li>
          <details>
            <summary>Parent</summary>
            <ul class="p-2">
              <li><a>Submenu 1</a></li>
              <li><a>Submenu 2</a></li>
            </ul>
          </details>
        </li>
        <li><a>Item 3</a></li>
      </ul>
    </div>
    <div class="navbar-end">
      <router-link
        to="/loginform"
        v-if="!auth.isLoggedIn"
        class="btn font-inter"
        >Login</router-link
      >
      <router-link
        to="/typeofaccount"
        v-if="!auth.isLoggedIn"
        class="btn font-inter"
        >Register</router-link
      >
    </div>
  </div>
</template>
