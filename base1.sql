

Create Table If Not Exists admi (
    Id Int Not Null Primary Key Auto_Increment,
    Names VarChar(25) Not Null,
    Email VarChar(35) Not Null unique,
    Pasword VarChar(255) Not Null
)Engine = InnoDB;

CREATE TABLE IF NOT EXISTS pasword (
    id INT AUTO_INCREMENT PRIMARY KEY,
    password VARCHAR(255) NOT NULL
);

Insert Into admi (Id, Names, Email, Pasword) 
Values (Null, 'Andministrador', 'administrador01@gmail.com', '$2y$10$CoQfAyDXwyuBkEvEEGtLZOUMQiD8bpv8Er5bFz.R7WVSv4XBojbZu'); -- admin1234

INSERT INTO pasword (password)
VALUES ('$2y$10$NvId2xdETOL9uEI.dcU.AemTfC9xoRjCG9LVvCQ1djgFGBq7wxUWW');-- 1234


CREATE TABLE IF NOT EXISTS especialidades (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(20) NOT NULL,
    especialidad VARCHAR(50) NOT NULL,
    descripcion VARCHAR(50) NOT NULL,

    ene_1era INT DEFAULT NULL,
    ene_sub INT DEFAULT NULL,
    feb_1era INT DEFAULT NULL,
    feb_sub INT DEFAULT NULL,
    mar_1era INT DEFAULT NULL,
    mar_sub INT DEFAULT NULL,
    abr_1era INT DEFAULT NULL,
    abr_sub INT DEFAULT NULL,
    may_1era INT DEFAULT NULL,
    may_sub INT DEFAULT NULL,
    jun_1era INT DEFAULT NULL,
    jun_sub INT DEFAULT NULL,
    jul_1era INT DEFAULT NULL,
    jul_sub INT DEFAULT NULL,
    ago_1era INT DEFAULT NULL,
    ago_sub INT DEFAULT NULL,
    sep_1era INT DEFAULT NULL,
    sep_sub INT DEFAULT NULL,
    oct_1era INT DEFAULT NULL,
    oct_sub INT DEFAULT NULL,
    nov_1era INT DEFAULT NULL,
    nov_sub INT DEFAULT NULL,
    dic_1era INT DEFAULT NULL,
    dic_sub INT DEFAULT NULL,

    anio INT NOT NULL
    );

CREATE TABLE IF NOT EXISTS manuales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    normatividad VARCHAR(255),
    nombre TEXT,
    anio VARCHAR(255),
    entidad TEXT,
    servicio TEXT,
    fecha VARCHAR(50),
    archivo VARCHAR(255),
    direccion VARCHAR(60)
);

CREATE TABLE IF NOT EXISTS paramedicos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(20) NOT NULL,
    especialidad VARCHAR(50) NOT NULL,
    division VARCHAR(50) NOT NULL,
    enero INT DEFAULT NULL,
    febrero INT DEFAULT NULL,
    marzo INT DEFAULT NULL,
    abril INT DEFAULT NULL,
    mayo INT DEFAULT NULL,
    junio INT DEFAULT NULL,
    julio INT DEFAULT NULL,
    agosto INT DEFAULT NULL,
    septiembre INT DEFAULT NULL,
    octubre INT DEFAULT NULL,
    noviembre INT DEFAULT NULL,
    diciembre INT DEFAULT NULL,
    anio INT NOT NULL
    );

CREATE TABLE IF NOT EXISTS urgencias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(20) NOT NULL,
    especialidad VARCHAR(50) NOT NULL,
    division VARCHAR(50) NOT NULL,
    enero INT DEFAULT NULL,
    febrero INT DEFAULT NULL,
    marzo INT DEFAULT NULL,
    abril INT DEFAULT NULL,
    mayo INT DEFAULT NULL,
    junio INT DEFAULT NULL,
    julio INT DEFAULT NULL,
    agosto INT DEFAULT NULL,
    septiembre INT DEFAULT NULL,
    octubre INT DEFAULT NULL,
    noviembre INT DEFAULT NULL,
    diciembre INT DEFAULT NULL,
    anio INT NOT NULL
    );

CREATE TABLE IF NOT EXISTS sitiosinteres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    descripcion VARCHAR(70),
    imagen VARCHAR(255),
    ruta VARCHAR(255)
    );

CREATE TABLE IF NOT EXISTS vencer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folio VARCHAR(10) NOT NULL,
    evento VARCHAR(12) NOT NULL,
    ini_paciente VARCHAR(12) NOT NULL,
    seguridad_social VARCHAR(15) NOT NULL,
    edad VARCHAR(20) NOT NULL,
    sexo VARCHAR(10) NOT NULL,
    diagnostico TEXT,
    fecha_evento DATE NOT NULL,
    fecha_noti DATE NOT NULL,
    turno VARCHAR(20) NOT NULL,
    servicio VARCHAR(50) NOT NULL,
    categoria VARCHAR(30) NOT NULL,
    proceso TEXT,
    definicion TEXT,
    descripcion TEXT,
    estatus VARCHAR(10),
    anio INT NOT NULL,
    UNIQUE(folio, evento, anio)
);

CREATE TABLE IF NOT EXISTS paciente (
    id INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    clave VARCHAR(20) NOT NULL,
    especialidad VARCHAR(50) NOT NULL,
    division VARCHAR(50) NOT NULL,
    enero INT DEFAULT NULL,
    febrero INT DEFAULT NULL,
    marzo INT DEFAULT NULL,
    abril INT DEFAULT NULL,
    mayo INT DEFAULT NULL,
    junio INT DEFAULT NULL,
    julio INT DEFAULT NULL,
    agosto INT DEFAULT NULL,
    septiembre INT DEFAULT NULL,
    octubre INT DEFAULT NULL,
    noviembre INT DEFAULT NULL,
    diciembre INT DEFAULT NULL,
    anio INT NOT NULL
    );

CREATE TABLE IF NOT EXISTS cirugia (
    id INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    clave VARCHAR(20) NOT NULL,
    especialidad VARCHAR(50) NOT NULL,
    division VARCHAR(50) NOT NULL,
    enero INT DEFAULT NULL,
    febrero INT DEFAULT NULL,
    marzo INT DEFAULT NULL,
    abril INT DEFAULT NULL,
    mayo INT DEFAULT NULL,
    junio INT DEFAULT NULL,
    julio INT DEFAULT NULL,
    agosto INT DEFAULT NULL,
    septiembre INT DEFAULT NULL,
    octubre INT DEFAULT NULL,
    noviembre INT DEFAULT NULL,
    diciembre INT DEFAULT NULL,
    anio INT NOT NULL
);

CREATE TABLE IF NOT EXISTS egresos (
    id INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    clave VARCHAR(20) NOT NULL,
    especialidad VARCHAR(50) NOT NULL,
    division VARCHAR(50) NOT NULL,
    enero INT DEFAULT NULL,
    febrero INT DEFAULT NULL,
    marzo INT DEFAULT NULL,
    abril INT DEFAULT NULL,
    mayo INT DEFAULT NULL,
    junio INT DEFAULT NULL,
    julio INT DEFAULT NULL,
    agosto INT DEFAULT NULL,
    septiembre INT DEFAULT NULL,
    octubre INT DEFAULT NULL,
    noviembre INT DEFAULT NULL,
    diciembre INT DEFAULT NULL,
    anio INT NOT NULL
);


CREATE TABLE IF NOT EXISTS ingresos (
    id INT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
    clave VARCHAR(20) NOT NULL,
    especialidad VARCHAR(50) NOT NULL,
    division VARCHAR(50) NOT NULL,
    enero INT DEFAULT NULL,
    febrero INT DEFAULT NULL,
    marzo INT DEFAULT NULL,
    abril INT DEFAULT NULL,
    mayo INT DEFAULT NULL,
    junio INT DEFAULT NULL,
    julio INT DEFAULT NULL,
    agosto INT DEFAULT NULL,
    septiembre INT DEFAULT NULL,
    octubre INT DEFAULT NULL,
    noviembre INT DEFAULT NULL,
    diciembre INT DEFAULT NULL,
    anio INT NOT NULL
);


CREATE TABLE IF NOT EXISTS personal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apaterno VARCHAR(100) NOT NULL,
    amaterno VARCHAR(100) NOT NULL,
    area VARCHAR(100),
    puesto VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    extension VARCHAR(10),
    correo VARCHAR(100),
    foto VARCHAR(255),
    jefe_id INT, -- Este campo referencia al superior directo (puede ser NULL)
    FOREIGN KEY (jefe_id) REFERENCES personal(id) ON DELETE SET NULL
) ENGINE = InnoDB;

-- TABLA NUEVA DE PRODUCTIVIDAD CONSULTA EXTERNA --

CREATE TABLE productividad_externa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    division VARCHAR(100) NOT NULL,
    especialidad VARCHAR(150) NOT NULL,
    matricula_medico VARCHAR(50) NOT NULL,
    consultorio VARCHAR(50) NOT NULL,
    fecha_atencion DATE NOT NULL,
    mes INT NOT NULL,
    anio INT NOT NULL,
    turno VARCHAR(50) NOT NULL,
    citado VARCHAR(20) NOT NULL,
    primera_vez VARCHAR(20) NOT NULL,
    diagnostico_principal VARCHAR(10) NOT NULL,
    clave_presupuestal VARCHAR(100) NOT NULL
);
