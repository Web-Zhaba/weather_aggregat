import useLocalStorage from "../hooks/useLocalStorage.js";
import { useState } from "react";
import styles from "./MainPage.module.css";

function show(value, unit) {
  return value === null || value === undefined ? "нет данных" : `${value} ${unit}`;
}

function SourceCard({ source, index }) {
  const style = { color: `var(--src-${(index % 5) + 1})` };

  if (source.error) {
    return (
      <div className={styles.card}>
        <h3 style={style}>{source.name}</h3>
        <p className={styles.muted}>Источник недоступен</p>
      </div>
    );
  }

  return (
    <div className={styles.card}>
      <h3 style={style}>{source.name}</h3>
      <p>Максимум: {show(source.temp_max, "°C")}</p>
      <p>Минимум: {show(source.temp_min, "°C")}</p>
      <p>Ветер: {show(source.wind_speed, "м/с")}</p>
      <p>Осадки: {show(source.precipitation, "мм")}</p>
      <p>Влажность: {show(source.humidity, "%")}</p>
    </div>
  );
}

function average(sources, field) {
  const values = sources.filter((s) => !s.error && s[field] != null).map((s) => s[field]);
  if (values.length === 0) return null;
  return (values.reduce((a, b) => a + b, 0) / values.length).toFixed(1);
}

function AverageCard({ sources }) {
  return (
    <div className={styles.card}>
      <h3>Среднее по источникам</h3>
      <p>Максимум: {show(average(sources, "temp_max"), "°C")}</p>
      <p>Минимум: {show(average(sources, "temp_min"), "°C")}</p>
      <p>Ветер: {show(average(sources, "wind_speed"), "м/с")}</p>
      <p>Осадки: {show(average(sources, "precipitation"), "мм")}</p>
      <p>Влажность: {show(average(sources, "humidity"), "%")}</p>
    </div>
  );
}

export default function MainPage() {
  const [city, setCity] = useLocalStorage("city");
  const [date, setDate] = useLocalStorage("date");
  const [country, setCountry] = useLocalStorage("country");
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);
  const [weatherData, setWeatherData] = useState(null);

  async function handleSubmit(event) {
    event.preventDefault();
    setError(null);
    setWeatherData(null);
    setLoading(true);

    const requestData = { city, ...(country && { country }), ...(date && { date }) };

    try {
      const response = await fetch(`/api/v1/weather?${new URLSearchParams(requestData)}`, {
        headers: { Accept: "application/json" },
      });
      const body = await response.json();
      if (response.ok) {
        setWeatherData(body);
      } else {
        setError(body.error || body.message || "Не удалось получить прогноз");
      }
    } catch {
      setError("Нет связи с сервером");
    } finally {
      setLoading(false);
    }
  }

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
          <button type="submit" name="search" disabled={loading}>Поиск</button>
        </form>
      </section>

      {loading && <p>Загрузка...</p>}
      {error && <p className={styles.error}>{error}</p>}

      {weatherData && (
        <section className="weather-data">
          <h2>
            {weatherData.city.name}, {weatherData.city.country} — {weatherData.date}
          </h2>
          <div className={styles.cards}>
            <AverageCard sources={weatherData.sources} />
            {weatherData.sources.map((source, i) => (
              <SourceCard key={source.source} source={source} index={i} />
            ))}
          </div>
                  </section>
      )}
    </div>
  );
}
