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
      },
      fontFamily: {
        inter: ["Inter", "sans-serif"],
        poppins: ["Poppins", "sans-serif"],
        epilogue: ["Epilogue", "sans-serif"],
      },
    },
  },
  plugins: [require("daisyui")],
};
