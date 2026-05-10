# Edara HSE Management System

Edara HSE is a comprehensive Health, Safety, and Environment (HSE) management platform designed to streamline safety protocols, permit workflows, and site reporting for industrial and construction environments. The system provides a unified interface across Web and Mobile platforms to ensure real-time safety compliance and data-driven decision-making.

---

## 🚀 Key Features

### 1. Permit to Work (PTW) Management
The core of the system, enabling full lifecycle management of work permits.
- **Permit Creation**: Digitalized workflow for requesting and approving permits.
- **History Tracking**: Complete audit trail of all permit modifications and status changes.
- **Visual Evidence**: Support for capturing and attaching images to permits.
- **Non-Compliance Tracking**: Identification and logging of safety violations related to active permits.
- **Automated Closures**: Streamlined process for finishing and archiving permits.

### 2. Daily Site Reporting
Automated tools for logging and analyzing daily operational activities.
- **Activity Logs**: Detailed recording of site progress and safety metrics.
- **Automated Analysis**: Backend processing to generate insights from daily reports.
- **Overview Dashboards**: High-level summaries for management review.

### 3. Observation & Comment System
A collaborative environment for reporting and resolving safety observations.
- **Real-time Logging**: Capture safety observations as they happen on-site.
- **Threaded Comments**: Interactive discussion for each observation to track resolution progress.
- **Resolution Workflow**: Dedicated status management for closing out open observations.

### 4. Advanced Analytics & Dashboards
Data visualization tools to monitor safety performance.
- **Dashboard Stats**: Real-time KPI tracking (Active Permits, Open Observations, etc.).
- **Ptw Analytics**: Specialized charts and reports for permit trends.
- **General Site Analytics**: Comprehensive data analysis for long-term safety planning.

### 5. User Management & Security
Granular control over system access and data integrity.
- **Role-Based Access Control (RBAC)**: Custom permissions for Supervisors, Managers, and Site Staff.
- **Secure Authentication**: Encrypted login with session management.
- **User Auditing**: Tracking user activities within the system.

### 6. Exporting & Documentation
Formal reporting capabilities for internal and external audits.
- **PDF Export**: Generate professional PDF reports for permits and site observations.
- **Excel Export**: Download data in spreadsheet format for further analysis.
- **Overdue Tracking**: Specific exports for identifying overdue safety tasks and permits.

---

## 📱 Multi-Platform Ecosystem

### Web Application (React)
A powerful administrative dashboard for management and data analysis.
- Built with **React** and **TypeScript**.
- Responsive design for desktop and tablet use.
- Interactive data tables and charting.

### Mobile Application (Flutter)
Designed for field workers and supervisors to use on the go.
- Built with **Flutter** using **Clean Architecture**.
- Offline-ready features for remote site locations.
- Seamless synchronization with the central database.

---

## 🛠️ Technical Stack

- **Backend**: PHP (API Layer)
- **Database**: MySQL (Relational Data Storage)
- **Frontend**: React.js, TypeScript, Vite
- **Mobile**: Flutter, Dart (Clean Architecture: Data, Domain, Presentation)
- **Styling**: CSS3, Tailwind CSS
- **Reporting**: TCPDF (for PDF generation), PHPSpreadsheet (for Excel)

---

## 📁 Project Structure

- `/api`: Core PHP backend endpoints.
- `/frontend`: React-based web dashboard.
- `/mobile`: Flutter mobile application.
- `/database`: SQL schemas and migration files.
- `/uploads`: Storage for permit images and report attachments.
