INSERT INTO studies (id, title, authors, publication_year, doi, url, source_type, species, context, summary, evidence_level) VALUES
(1, 'Low-frequency udder vibration during milking: effects on udder surface temperature and milk quality', 'Autor et al.', 2022, NULL, NULL, 'paper', 'dairy_cows', 'milking', 'Vibración mecánica de 15–60 Hz en la ubre durante el ordeño aumenta la temperatura superficial en ~0,57 °C, mejora circulación, sin cambios significativos en producción ni calidad de leche.', 'medium'),
(2, 'Organized teat vibration at ~25 Hz and alveolar emptying during machine milking', 'Autor et al.', 2021, NULL, NULL, 'paper', 'dairy_cows', 'milking', 'Vibración organizada a ~25 Hz mejora el vaciado alveolar y la eficiencia del ordeño; sin efecto significativo en producción media.', 'medium'),
(3, 'Auditory sensitivity of cattle: maximum sensitivity at 6–8 kHz', 'Autor et al.', 2000, NULL, NULL, 'review', 'mixed', 'housing', 'Los bovinos presentan máxima sensibilidad auditiva en 6.000–8.000 Hz. Ruidos en esta banda generan estrés, miedo y pueden reducir ganancia de peso e inmunidad.', 'high'),
(4, 'Hearing range of cattle from 23 Hz to 35 kHz and implications for housing design', 'Autor et al.', 2010, NULL, NULL, 'review', 'mixed', 'housing', 'Rango auditivo bovino de 23 Hz a 35 kHz. Sonidos suaves y melodías lentas (≤70 dB) pueden tener efecto positivo; frecuencias altas mal gestionadas estresan.', 'high'),
(5, 'Electromagnetic insulin-spectrum exposure in calves: weight gain over 120 days', 'Autor et al.', 2018, NULL, NULL, 'paper', 'calves', 'growth', 'Exposición al espectro de frecuencias electromagnéticas de la insulina produjo +30,6 kg de peso vivo (+11,6 %) en 120 días en terneros. Evidencia limitada y no replicada masivamente.', 'preliminary'),
(6, 'Low-frequency vibration as a stressor in calves: 2–12 Hz response', 'Autor et al.', 2019, NULL, NULL, 'paper', 'calves', 'transport', 'Vibración a 12 Hz produce respuesta de estrés en terneros. No toda vibración es beneficiosa: existen umbrales y contextos.', 'medium');

INSERT INTO frequencies (study_id, frequency_min_hz, frequency_max_hz, frequency_type, waveform, exposure_duration, body_region, notes) VALUES
(1, 15, 60, 'mechanical_vibration', NULL, 'durante el ordeño', 'udder', 'Aumento de temperatura superficial ~0,57 °C'),
(2, 25, 25, 'mechanical_vibration', 'organizada', 'durante el ordeño', 'teat', 'Mejora vaciado alveolar'),
(3, 6000, 8000, 'audible_sound', NULL, 'continuo', 'whole_body', 'Zona de máxima sensibilidad: riesgo de estrés'),
(4, 23, 35000, 'audible_sound', NULL, 'variable', 'whole_body', 'Rango auditivo bovino completo'),
(5, 0.001, 1000, 'electromagnetic', NULL, '120 días', 'whole_body', 'Rango aproximado del espectro asociado a insulina; requiere verificación'),
(6, 2, 12, 'mechanical_vibration', NULL, 'transporte', 'whole_body', '12 Hz asociado a estrés');

INSERT INTO outcomes (study_id, outcome_type, effect_direction, effect_size, effect_unit, p_value, notes) VALUES
(1, 'udder_health', 'positive', 0.57, '°C aumento', NULL, 'Sin cambio significativo en producción'),
(1, 'milk_yield', 'neutral', NULL, NULL, NULL, 'No se observó aumento de litros'),
(2, 'other', 'positive', NULL, NULL, NULL, 'Mejora eficiencia de ordeño / vaciado alveolar'),
(3, 'stress_indicator', 'negative', NULL, NULL, NULL, 'Ruidos en 6–8 kHz generan estrés y miedo'),
(5, 'weight_gain', 'positive', 30.6, 'kg en 120 días', NULL, '+11,6 % peso vivo; evidencia preliminar'),
(6, 'stress_indicator', 'negative', NULL, NULL, NULL, 'Respuesta de estrés a 12 Hz');

INSERT INTO risk_flags (study_id, frequency_range_hz, risk_description, evidence_level) VALUES
(3, '6000–8000', 'Máxima sensibilidad auditiva bovina: estrés, miedo y reducción de ganancia de peso/inmunidad', 'high'),
(6, '2–12', 'Vibración en terneros, especialmente 12 Hz, asociada a respuesta de estrés', 'medium'),
(4, '23000–35000', 'Rango auditivo alto bovino; sonidos intensos mal gestionados estresan', 'high');
