import useLocalStorage from "../hooks/useLocalStorage.js";
import { useState } from "react";

export default function MainPage() {
  const [city, setCity] = useLocalStorage("city");
  const [date, setDate] = useLocalStorage("date");
  const [country, setCountry] = useLocalStorage("country");
  const [error, setError] = useState(null);
  const [weatherData, setWeatherData] = useState(null);
  
  async function handleSubmit(event) {
    event.preventDefault();
    setError(null);
    setWeatherData(null);
    const requestData = { "city": city, ...(country && { "country": country }), ...(date && { "date": date }) };
    const response = await fetch(`/api/v1/weather?${new URLSearchParams(requestData).toString()}`, {
      headers: { Accept: "application/json" },
    });
    const body = await response.json();
    if (response.ok) {
      setWeatherData(body);
    } else {
      setError(body.error);
    }
  };

  return (
    <div>
      <section className="main-page">
        <h1>Введите город</h1>
        <form onSubmit={handleSubmit}>
          <label>
            Город:
            <input type="text" name="city" value={city} required placeholder="Название города" onChange={(e) => setCity(e.target.value)} />
          </label>
          <label>
            Страна:
            <input type="text" name="country" value={country} placeholder="Название страны" onChange={(e) => setCountry(e.target.value)} />
          </label>
          <label>
            Дата:
            <input type="date" name="date" value={date} onChange={(e) => setDate(e.target.value)} />
          </label>
          <button type="submit" name="search">Поиск</button>
        </form>
      </section>
      {error && <p style={{ color: "red" }}>{error}</p>}
      {weatherData && (
        <section className="weather-data">
          <h2>Данные о погоде</h2>
          {weatherData.city && (
            <p>Город: {weatherData.city.name}, {weatherData.city.country}</p>
          )}
          <p>Часовой пояс: {weatherData.timezone}</p>
          <p>Дата: {weatherData.daily.time[0]}</p>
          <p>Максимальная температура: {weatherData.daily.temperature_2m_max[0]}°C</p>
          <p>Минимальная температура: {weatherData.daily.temperature_2m_min[0]}°C</p>
          <p>Осадки: {weatherData.daily.precipitation_sum[0]} мм</p>
        </section>
      )}
    </div>
  );
}