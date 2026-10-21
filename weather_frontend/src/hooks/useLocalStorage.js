import { useState, useEffect } from "react";

export default function useLocalStorage(key) {

    const [value, setValue] = useState(() => {
      try {
        return localStorage.getItem(key) ?? "";
      } catch {
        return ""
      }
    });

      useEffect(() => {
      try{
        value !== "" ? localStorage.setItem(key, value) : localStorage.removeItem(key);
      } catch (error) {
        console.error("Ошибка при работе с localStorage:", error);}
    }, [key, value]);

  return [value, setValue];
}