# TaskTracker - Sistema de Seguimiento de Tareas

API RESTful desarrollada en **Laravel** para la gestión y seguimiento de tareas de empleados. Los administradores crean y asignan tareas, mientras que los empleados las realizan, actualizan su estado y envían evidencia de su progreso. El frontend móvil se consume desde una app en **React Native**.

## Tecnologías

- **Backend:** Laravel (PHP)
- **Base de Datos:** MySQL
- **Frontend Móvil:** React Native (consumo de API)
- **Autenticación:** Laravel Sanctum (Tokens)

## Funcionalidades

- **Administrador:**
  - Crear, editar y eliminar tareas.
  - Asignar tareas a empleados.
  - Ver el progreso y evidencias enviadas.

- **Empleado:**
  - Ver tareas asignadas.
  - Actualizar estado de la tarea (Pendiente, En Progreso, Completada).
  - Subir evidencia (imágenes/documentos) del avance.
