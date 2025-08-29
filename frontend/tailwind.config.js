/** @type {import('tailwindcss').Config} */

export default {
  content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
  theme: {
    extend: {
      colors: {
        "dark-slate": "#44576D",
        customblue: "#59708C",
        customdarkblue: "#415367",
        customButton: "#6682A3",
        "blue-gray": "#F6F6F6",
        blue: {
          600: "#2563eb",
          700: "#1d4ed8",
        },
        gray: {
          600: "#4b5563",
        },
        red: {
          600: "#dc2626",
        },
        white: "#ffffff",
        black: "#000000",
      },
      fontFamily: {
        inter: ["Inter", "sans-serif"],
        poppins: ["Poppins", "sans-serif"],
        epilogue: ["Epilogue", "sans-serif"],
      },
    },
  },
  plugins: [
    require("daisyui"), // ✅ keep this, DaisyUI v3 still works
  ],
  future: {
    disableColorOpacityUtilitiesByDefault: true,
  },
  experimental: {
    optimizeUniversalDefaults: true,
  },
  corePlugins: {
    preflight: true, // keep Tailwind reset
  },
};
