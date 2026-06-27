import { Moon, Sun } from "lucide-react";
import { useTheme } from "../../shared/contexts/ThemeContext";

export function ThemeToggle() {
  const { theme, setTheme } = useTheme();

  return (
    <button
      onClick={() => setTheme(theme === "dark" ? "light" : "dark")}
      className="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 relative rounded-lg hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors flex items-center justify-center"
      title="Toggle theme"
    >
      <Sun size={18} className="rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
      <Moon size={18} className="absolute rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
      <span className="sr-only">Toggle theme</span>
    </button>
  );
}
