# Inventario de rutas — extraído de Viajes.docx

**Fuente:** `Viajes.docx` (aportado por el usuario, 2026-09-11). Extraído directamente del
XML del documento (no es una tabla de Word, son párrafos por día con líneas de guion).
**Estado:** Información parcial y valiosa — da el patrón semanal de rutas y sus paradas,
pero **no** da los kilómetros. El usuario y su papá medirán `standard_km` manualmente más
adelante; hasta entonces estas rutas **no se siembran** en la tabla `routes` (que exige
`standard_km` no nulo, ADR 0002) para no inventar el dato.

---

## Calendario semanal, tal como aparece en el documento

| Día | # | Paradas / destino | Lectura probable |
|---|---|---|---|
| Viernes | 1 | Escuela Sabana, Liceo de Tarrazú, Escuela Manuel Castro, C.T.P San Pablo, Escuela Bolivia, C.T.P Daniel Flores | Ruta escolar/CEN — zona San Pablo–Tarrazú |
| Viernes | 2 | Megasuper | Entrega a supermercado |
| Viernes | 3 | Walmart CoopeDota | Entrega a supermercado (recurrente, ver abajo) |
| Sábado | 4 | Walmart Coopetarrazú | Entrega a supermercado |
| Sábado | 5 | Auto Mercado | Entrega a supermercado |
| Lunes | 6 | Esc. Mixta San Cristóbal Sur, Esc. San Cristóbal Norte, Liceo Llano Los Ángeles, CEN Llano Los Ángeles, CEN San Cristóbal Norte, CEN San Isidro Del Guarco, CEN Tejar, CEN Purires, CEN Palmital, Esc. Félix Mata | Ruta escolar/CEN — zona El Guarco |
| Lunes | 7 | Colegio Monterrey, Esc. Limonal, CEN/Esc. el Rosario, CEN San Juan Sur, CEN San Juan Norte, CEN de Bustamante, CEN Loma Larga | Ruta escolar/CEN — zona Monterrey/Rosario |
| Lunes | 8 | Esc. de San Marcos, Liceo de Tarrazú, Esc. la Sabana, C.T.P San Pablo | Ruta escolar/CEN — se solapa con la #1 |
| Lunes | 9 | C.T.P Dota, Cecudi Santa María, CEN Santa María, Esc. Bolivia, Esc. Rodeo | Ruta escolar/CEN — zona Dota |
| Lunes | 10 | CEN de San Pablo, CEN de San Isidro, Esc. de San Isidro, CEN Llano Bonito, Esc. Manuel Castro | Ruta escolar/CEN — zona San Pablo/Llano Bonito |
| Lunes | 11 | CEN San Lorenzo, CEN San Marcos, CEN Empalme, CEN Frailes, CEN Santa Cruz, Esc. Camilo Gamboa Vargas, Liceo Santa Cruz, Esc. de San Marcos, Esc. la Lucha del Guarco, CEN San Marcos | Ruta escolar/CEN — zona San Marcos/Frailes/Santa Cruz |
| Lunes | 12 | Delegación Dota, Delegación Tarrazú, Delegación San Pablo | Entrega a delegaciones policiales |
| Lunes | 13 | Walmart CoopeDota | Entrega a supermercado (recurrente) |
| Martes | 14 | CEN San Antonio, Esc. San Antonio | Ruta escolar/CEN |
| Martes | 15 | Esc. la Entrada, Esc. Cecilia Orlich, Esc. de Santa Elena, Colegio de Corralillo, CEN Santa Elena, CEN San Antonio de Corralillo, CEN Corralillo, Esc. Corralillo, Esc. San Joaquín, CEN El Alumbre, Esc. la Violeta, Esc. San Juan Sur, Esc. Río Conejo | Ruta escolar/CEN — zona Corralillo |
| Martes | 16 | Cartón | **No es destino de entrega** — parece recolección de cartón (posible ingreso extra / reciclaje) |
| Miércoles | 17 | Walmart CoopeDota | Entrega a supermercado (recurrente) |
| Miércoles | 18 | Ruta Coralillo | Nombre propio de ruta — ¿es la misma zona que la #15? |
| Jueves | 19 | Ruta Parritilla (Willy) | Nombre propio de ruta + nombre de persona entre paréntesis — ¿conductor? |

No aparece ningún domingo.

## Observaciones (hipótesis a confirmar, no hechos)

1. **Lunes concentra 8 rutas escolares/CEN distintas** (#6–13) — casi coincide con el
   tamaño de la flota (6 camiones + 1 pickup). Hipótesis: los lunes salen varios o todos
   los camiones a la vez, cada uno a una zona distinta, para abastecer escuelas/CEN-CINAI.
2. **"Walmart CoopeDota" se repite 3 veces por semana** (viernes, lunes, miércoles) —
   probablemente es un solo cliente con contrato de entregas recurrentes, no tres clientes
   distintos.
3. **Al menos tres tipos de cliente distintos parecen mezclarse:**
   - Escuelas / CEN-CINAI (posible contrato institucional único — MEP, municipalidad o
     programa de comedores escolares — o varios contratos por zona).
   - Supermercados (Walmart CoopeDota, Walmart Coopetarrazú, Megasuper, Auto Mercado) —
     estos sí parecen clientes comerciales independientes entre sí.
   - Delegaciones policiales (Dota, Tarrazú, San Pablo).
   - Cartón — posible actividad aparte (venta/reciclaje), no un cliente de flete.
4. **"Ruta Coralillo" (miércoles) podría ser la misma zona que la ruta #15 (martes,
   Corralillo)** — mismo lugar, ¿es la misma ruta repetida o dos rutas distintas?
5. **"Willy" junto a "Ruta Parritilla"** parece un nombre de conductor. Aún no existe
   ningún conductor en el sistema — no se crea el registro sin sus datos completos
   (`drivers.hourly_rate` es obligatorio y no lo tenemos).
6. Todas las rutas están en la zona de Los Santos / El Guarco (Tarrazú, Dota, San Pablo,
   San Marcos, San Isidro del Guarco, Corralillo) — geografía consistente con la flota de
   7 unidades operando en una sola región.

## Lo que este documento NO da (y sigue pendiente)

- **Kilómetros estándar por ruta** (`routes.standard_km`) — el usuario y su papá lo
  medirán manualmente. Sin esto no se puede sembrar `routes` (columna obligatoria,
  ADR 0002) ni calcular ningún KPI por km para estas rutas.
- Origen/depósito exacto (punto de partida de cada ruta).
- Si cada línea con guion es **una ruta continua** (todas las paradas en un mismo viaje,
  en ese orden) o **varias entregas independientes** el mismo día.
- Identidad exacta del/los cliente(s) detrás de las paradas escolares/CEN.
- Costo de peaje típico por ruta.

## Siguiente paso sugerido (no ejecutado aún)

Cuando tengan las distancias, esta tabla ya está lista para convertirse directamente en
filas de `routes` (nombre = zona/cliente, origen, destino final o "ruta circular con N
paradas", `standard_km`). No hace falta rehacer el levantamiento — solo completar la
columna de km.
