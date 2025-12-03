import js from "@eslint/js";
import globals from "globals";
import { defineConfig } from "eslint/config";

export default defineConfig([
  { files: ["**/*.{js,mjs,cjs}"], plugins: { js }, extends: ["js/recommended"], languageOptions: { globals: globals.browser } },
  { files: ["**/*.js"], languageOptions: { sourceType: "script" } },
  {
    rules: {
      // Variables can be injected from within PHP files, thus we disable this rule.
      'no-undef': 'off',
      // Unused variables must follow this specific pattern.
      'no-unused-vars': [
        'warn',
        {
          argsIgnorePattern: '^_',
          varsIgnorePattern: '^_',
          caughtErrorsIgnorePattern: '^_'
        }
      ],
    }
  },
  {
    files: ["modules/registrars/realtimeregister/src/Assets/Js/adac.js", "modules/registrars/realtimeregister/src/Assets/Js/util.js"],
    rules: {
      'no-unused-vars': 'off',
    }
  },
]);
