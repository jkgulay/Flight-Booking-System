--
-- PostgreSQL database dump
--

-- Dumped from database version 16.1
-- Dumped by pg_dump version 16.1

-- Started on 2024-12-23 04:51:24

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 236 (class 1255 OID 26976)
-- Name: after_user_insert_func(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.after_user_insert_func() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO logs (user_id, action_type, table_name, affected_columns, details)
    VALUES (NEW.id, 'INSERT', 'users', 'ALL', 'New user added');
    RETURN NEW; -- Return the new row
END;
$$;


ALTER FUNCTION public.after_user_insert_func() OWNER TO postgres;

--
-- TOC entry 237 (class 1255 OID 26977)
-- Name: after_user_update_func(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.after_user_update_func() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO logs (user_id, action_type, table_name, affected_columns, details)
    VALUES (NEW.id, 'UPDATE', 'users', 'Updated columns', 'User  details updated');
    RETURN NEW; -- Return the updated row
END;
$$;


ALTER FUNCTION public.after_user_update_func() OWNER TO postgres;

--
-- TOC entry 238 (class 1255 OID 26978)
-- Name: before_user_delete_func(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.before_user_delete_func() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO logs (user_id, action_type, table_name, affected_columns, details)
    VALUES (OLD.id, 'DELETE', 'users', 'ALL', 'User  deleted');
    RETURN OLD; -- Return the old row
END;
$$;


ALTER FUNCTION public.before_user_delete_func() OWNER TO postgres;

--
-- TOC entry 233 (class 1255 OID 26815)
-- Name: get_booking_count_by_flight(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_booking_count_by_flight(flight_id_param bigint) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
DECLARE
    booking_count BIGINT;
BEGIN
    SELECT COUNT(bf.id) INTO booking_count
    FROM booked_flight bf
    WHERE bf.flight_id = flight_id_param; 

    RETURN booking_count;
END;
$$;


ALTER FUNCTION public.get_booking_count_by_flight(flight_id_param bigint) OWNER TO postgres;

--
-- TOC entry 234 (class 1255 OID 26968)
-- Name: get_flight_price(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_flight_price(flight_id bigint) RETURNS double precision
    LANGUAGE plpgsql
    AS $$
DECLARE
    flight_price DOUBLE PRECISION;
BEGIN
    SELECT price INTO flight_price FROM flight_list WHERE id = flight_id;
    RETURN flight_price;
END;
$$;


ALTER FUNCTION public.get_flight_price(flight_id bigint) OWNER TO postgres;

--
-- TOC entry 235 (class 1255 OID 26969)
-- Name: log_booking_activity(bigint, character varying, character varying, text, text); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.log_booking_activity(user_id bigint, action_type character varying, table_name character varying, affected_columns text, details text) RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
    INSERT INTO logs (user_id, action_type, timestamp, table_name, affected_columns, details)
    VALUES (user_id, action_type, NOW(), table_name, affected_columns, details);
END;
$$;


ALTER FUNCTION public.log_booking_activity(user_id bigint, action_type character varying, table_name character varying, affected_columns text, details text) OWNER TO postgres;

--
-- TOC entry 239 (class 1255 OID 26970)
-- Name: refresh_airline_booking_summary(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.refresh_airline_booking_summary() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Create the table if it doesn't exist
    CREATE TABLE IF NOT EXISTS airline_booking_summary_materialized (
        airlines TEXT,
        total_bookings INTEGER
    );

    -- Clear existing data
    TRUNCATE TABLE airline_booking_summary_materialized;

    -- Insert new data
    INSERT INTO airline_booking_summary_materialized (airlines, total_bookings)
    SELECT 
        a.airlines,
        COUNT(b.id) AS total_bookings
    FROM 
        booked_flight b
    JOIN 
        flight_list f ON b.flight_id = f.id
    JOIN 
        airlines_list a ON f.airline_id = a.id
    GROUP BY 
        a.airlines
    ORDER BY 
        total_bookings DESC;
END;
$$;


ALTER FUNCTION public.refresh_airline_booking_summary() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 224 (class 1259 OID 26862)
-- Name: airlines_list; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.airlines_list (
    id bigint NOT NULL,
    airlines text NOT NULL,
    logo_path text NOT NULL
);


ALTER TABLE public.airlines_list OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 26982)
-- Name: booked_flight_new_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.booked_flight_new_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.booked_flight_new_id_seq OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 26921)
-- Name: booked_flight; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.booked_flight (
    user_id bigint NOT NULL,
    flight_id bigint NOT NULL,
    name text NOT NULL,
    address text NOT NULL,
    contact text NOT NULL,
    status character varying(10) DEFAULT 'pending'::character varying,
    id bigint DEFAULT nextval('public.booked_flight_new_id_seq'::regclass) NOT NULL,
    CONSTRAINT booked_flight_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'accepted'::character varying, 'decline'::character varying, 'declined'::character varying])::text[])))
);


ALTER TABLE public.booked_flight OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 26898)
-- Name: flight_list; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.flight_list (
    id bigint NOT NULL,
    airline_id bigint NOT NULL,
    plane_no character varying(50) NOT NULL,
    departure_airport_id bigint NOT NULL,
    arrival_airport_id bigint NOT NULL,
    departure_datetime timestamp without time zone NOT NULL,
    arrival_datetime timestamp without time zone NOT NULL,
    seats integer DEFAULT 0 NOT NULL,
    price numeric(10,2) NOT NULL,
    date_created timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.flight_list OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 26940)
-- Name: airline_booking_summary; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.airline_booking_summary AS
 SELECT a.airlines,
    count(b.id) AS total_bookings
   FROM ((public.booked_flight b
     JOIN public.flight_list f ON ((b.flight_id = f.id)))
     JOIN public.airlines_list a ON ((f.airline_id = a.id)))
  GROUP BY a.airlines
  ORDER BY (count(b.id)) DESC
  WITH NO DATA;


ALTER MATERIALIZED VIEW public.airline_booking_summary OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 26997)
-- Name: airline_booking_summary_materialized; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.airline_booking_summary_materialized (
    airlines text,
    total_bookings integer
);


ALTER TABLE public.airline_booking_summary_materialized OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 26861)
-- Name: airlines_list_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.airlines_list_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.airlines_list_id_seq OWNER TO postgres;

--
-- TOC entry 4947 (class 0 OID 0)
-- Dependencies: 223
-- Name: airlines_list_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.airlines_list_id_seq OWNED BY public.airlines_list.id;


--
-- TOC entry 222 (class 1259 OID 26853)
-- Name: airport_list; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.airport_list (
    id bigint NOT NULL,
    airport text NOT NULL,
    location text NOT NULL
);


ALTER TABLE public.airport_list OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 26852)
-- Name: airport_list_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.airport_list_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.airport_list_id_seq OWNER TO postgres;

--
-- TOC entry 4948 (class 0 OID 0)
-- Dependencies: 221
-- Name: airport_list_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.airport_list_id_seq OWNED BY public.airport_list.id;


--
-- TOC entry 229 (class 1259 OID 26947)
-- Name: booked_flight_summary; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.booked_flight_summary AS
 SELECT bf.id AS booking_id,
    bf.name,
    bf.contact,
    f.departure_datetime,
    f.arrival_datetime,
    a.airlines
   FROM ((public.booked_flight bf
     JOIN public.flight_list f ON ((bf.flight_id = f.id)))
     JOIN public.airlines_list a ON ((f.airline_id = a.id)));


ALTER VIEW public.booked_flight_summary OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 26952)
-- Name: flight_details; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.flight_details AS
 SELECT f.id AS flight_id,
    a.airlines,
    ap1.airport AS departure_airport,
    ap2.airport AS arrival_airport,
    f.departure_datetime,
    f.arrival_datetime,
    f.price,
    f.seats
   FROM (((public.flight_list f
     JOIN public.airlines_list a ON ((f.airline_id = a.id)))
     JOIN public.airport_list ap1 ON ((f.departure_airport_id = ap1.id)))
     JOIN public.airport_list ap2 ON ((f.arrival_airport_id = ap2.id)));


ALTER VIEW public.flight_details OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 26897)
-- Name: flight_list_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.flight_list_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.flight_list_id_seq OWNER TO postgres;

--
-- TOC entry 4949 (class 0 OID 0)
-- Dependencies: 225
-- Name: flight_list_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.flight_list_id_seq OWNED BY public.flight_list.id;


--
-- TOC entry 220 (class 1259 OID 26838)
-- Name: logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.logs (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    action_type character varying(50),
    "timestamp" timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    table_name character varying(50),
    affected_columns text,
    details text
);


ALTER TABLE public.logs OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 26837)
-- Name: logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.logs_id_seq OWNER TO postgres;

--
-- TOC entry 4950 (class 0 OID 0)
-- Dependencies: 219
-- Name: logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.logs_id_seq OWNED BY public.logs.id;


--
-- TOC entry 218 (class 1259 OID 26829)
-- Name: system_settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.system_settings (
    id bigint NOT NULL,
    name text NOT NULL,
    email character varying(200) NOT NULL,
    contact character varying(20) NOT NULL,
    cover_img text NOT NULL,
    about_content text NOT NULL
);


ALTER TABLE public.system_settings OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 26828)
-- Name: system_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.system_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.system_settings_id_seq OWNER TO postgres;

--
-- TOC entry 4951 (class 0 OID 0)
-- Dependencies: 217
-- Name: system_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.system_settings_id_seq OWNED BY public.system_settings.id;


--
-- TOC entry 216 (class 1259 OID 26817)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(200) NOT NULL,
    address text NOT NULL,
    contact text NOT NULL,
    username character varying(100) NOT NULL,
    password character varying(200) NOT NULL,
    type smallint DEFAULT 2 NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT users_type_check CHECK ((type = ANY (ARRAY[1, 2, 3])))
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 215 (class 1259 OID 26816)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- TOC entry 4952 (class 0 OID 0)
-- Dependencies: 215
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 4748 (class 2604 OID 26865)
-- Name: airlines_list id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.airlines_list ALTER COLUMN id SET DEFAULT nextval('public.airlines_list_id_seq'::regclass);


--
-- TOC entry 4747 (class 2604 OID 26856)
-- Name: airport_list id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.airport_list ALTER COLUMN id SET DEFAULT nextval('public.airport_list_id_seq'::regclass);


--
-- TOC entry 4749 (class 2604 OID 26901)
-- Name: flight_list id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.flight_list ALTER COLUMN id SET DEFAULT nextval('public.flight_list_id_seq'::regclass);


--
-- TOC entry 4745 (class 2604 OID 26841)
-- Name: logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logs ALTER COLUMN id SET DEFAULT nextval('public.logs_id_seq'::regclass);


--
-- TOC entry 4744 (class 2604 OID 26832)
-- Name: system_settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.system_settings ALTER COLUMN id SET DEFAULT nextval('public.system_settings_id_seq'::regclass);


--
-- TOC entry 4741 (class 2604 OID 26820)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 4941 (class 0 OID 26997)
-- Dependencies: 232
-- Data for Name: airline_booking_summary_materialized; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.airline_booking_summary_materialized (airlines, total_bookings) FROM stdin;
Cebu Pacific	2
\.


--
-- TOC entry 4935 (class 0 OID 26862)
-- Dependencies: 224
-- Data for Name: airlines_list; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.airlines_list (id, airlines, logo_path) FROM stdin;
1	AirAsia	1600999080_kisspng-flight-indonesia-airasia-airasia-japan-airline-tic-asia-5abad146966736.8321896415221927106161.jpg
2	Philippine Airlines	1600999200_Philippine-Airlines-Logo.jpg
3	Cebu Pacific	1600999200_43cada0008538e3c1a1f4675e5a7aabe.jpeg
5	Cebu Mactan	1734635640_5-removebg-preview.png
14	Bachelor Express	1734637680_1.png
\.


--
-- TOC entry 4933 (class 0 OID 26853)
-- Dependencies: 222
-- Data for Name: airport_list; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.airport_list (id, airport, location) FROM stdin;
2	Beijing Capital International Airport	Chaoyang-Shunyi, Beijing
3	Los Angeles International Airport	Los Angeles, California
4	Dubai International Airport	Garhoud, Dubai
5	Mactan-Cebu Airport	Cebu
1	NAIA	Metro Manila
6	Bancasi Airport	Butuan City
\.


--
-- TOC entry 4938 (class 0 OID 26921)
-- Dependencies: 227
-- Data for Name: booked_flight; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.booked_flight (user_id, flight_id, name, address, contact, status, id) FROM stdin;
18	11	Admin	p-5,poblacion 7, buenavista adn.	09100290521	accepted	2
18	11	Jun Kyle Armecin Gulay	p-5,poblacion 7, buenavista adn.	123456	accepted	3
\.


--
-- TOC entry 4937 (class 0 OID 26898)
-- Dependencies: 226
-- Data for Name: flight_list; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.flight_list (id, airline_id, plane_no, departure_airport_id, arrival_airport_id, departure_datetime, arrival_datetime, seats, price, date_created) FROM stdin;
5	2	02-141	6	5	2024-12-25 03:30:00	2024-12-27 03:30:00	50	1010.00	2024-12-23 03:30:32.598024
9	1	23	3	4	2024-12-28 03:32:00	2024-12-30 03:32:00	230	78140.00	2024-12-23 03:33:11.504357
11	3	1416	5	6	2024-12-19 03:41:00	2024-12-26 03:41:00	230	2300.00	2024-12-23 03:41:45.928236
14	2	75843-12412	5	6	2024-12-24 04:49:00	2024-12-25 04:49:00	200	2146.00	2024-12-23 04:49:48.637219
\.


--
-- TOC entry 4931 (class 0 OID 26838)
-- Dependencies: 220
-- Data for Name: logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.logs (id, user_id, action_type, "timestamp", table_name, affected_columns, details) FROM stdin;
4	17	INSERT	2024-12-23 01:56:00.020747	users	ALL	New user added
5	18	INSERT	2024-12-23 01:56:00.020747	users	ALL	New user added
6	25	INSERT	2024-12-23 01:56:00.020747	users	ALL	New user added
7	40	INSERT	2024-12-23 01:56:00.020747	users	ALL	New user added
10	6	INSERT	2024-12-23 02:09:42.332166	users	ALL	New user added
15	6	UPDATE	2024-12-23 02:29:34.990376	users	Updated columns	User  details updated
18	9	INSERT	2024-12-23 03:02:55.576347	users	ALL	New user added
19	11	INSERT	2024-12-23 04:34:30.20605	users	ALL	New user added
20	11	UPDATE	2024-12-23 04:35:05.611812	users	Updated columns	User  details updated
24	13	INSERT	2024-12-23 04:39:03.911934	users	ALL	New user added
\.


--
-- TOC entry 4929 (class 0 OID 26829)
-- Dependencies: 218
-- Data for Name: system_settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.system_settings (id, name, email, contact, cover_img, about_content) FROM stdin;
1	Online Flight Booking System	info@sample.com	+6948 8542 623	1600998360_travel-cover.jpg	Lorem Ipsum description.
\.


--
-- TOC entry 4927 (class 0 OID 26817)
-- Dependencies: 216
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, address, contact, username, password, type, created_at) FROM stdin;
17	Gummy Worms	p-5,poblacion 7, buenavista adn.	09876543211	katzukii21	$2y$10$soSSUffvO3pYKD.q0Rjp9eTJ6UGMSqiYAH2lfcst2loUEbWbdNl7a	3	2024-12-23 00:32:12.599778
18	Admin	Butuan City	09100290521	admin	$2y$10$EeMbofo.QX1UZSaRQIlvd.rESdVKoSNOIZ9GDVL1DA52aUBoc2q2i	1	2024-12-23 00:32:12.599778
25	Aldwin	Test Address	1234567890	testuser	password123	3	2024-12-23 00:32:12.599778
40	Jun Kyle Gulay	Butuan City	09100290521	kayel2002	$2y$10$GIEhpWbRJ7OR8Js/TefNMuOTXdE9WIWBQNF/5Oz5naYEhvSUhSzMK	2	2024-12-23 00:32:12.599778
6	Jun Kyle Armecin Gulay	p-5,poblacion 7, buenavista adn.	09100290521	232	$2y$10$T9EypM/V/1bz3RXEPthMw.KTp6Ayv4URpHrayXM32DIdm2g5GgDFC	2	2024-12-23 02:09:42.332166
9	Robert Palma	BVutaun	85454546	roberto123	$2y$10$UhyqfNlH/Cly021Dv4BVTeWLs55y/4E2aDluLccbZ4nJzeAd.3ofW	3	2024-12-23 03:02:55.576347
11	James Espana	Ampayon, Butuan City	2325151	jamesxcz	$2y$10$/laQRyqKEyUgUrHV3WRfJuRykXLn4Q8VYKFBppt50sN2BQ8RdFjHa	3	2024-12-23 04:34:30.20605
13	Dr. Stone Robersts	Butuan City	09100290521	Stoners	$2y$10$.1lzoEcMS8FZ7e4WBfV5qOGKFGhgKhPPM/uHY430hPqcNoqSjZW.C	3	2024-12-23 04:39:03.911934
\.


--
-- TOC entry 4953 (class 0 OID 0)
-- Dependencies: 223
-- Name: airlines_list_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.airlines_list_id_seq', 10, true);


--
-- TOC entry 4954 (class 0 OID 0)
-- Dependencies: 221
-- Name: airport_list_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.airport_list_id_seq', 16, true);


--
-- TOC entry 4955 (class 0 OID 0)
-- Dependencies: 231
-- Name: booked_flight_new_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.booked_flight_new_id_seq', 3, true);


--
-- TOC entry 4956 (class 0 OID 0)
-- Dependencies: 225
-- Name: flight_list_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.flight_list_id_seq', 14, true);


--
-- TOC entry 4957 (class 0 OID 0)
-- Dependencies: 219
-- Name: logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.logs_id_seq', 25, true);


--
-- TOC entry 4958 (class 0 OID 0)
-- Dependencies: 217
-- Name: system_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.system_settings_id_seq', 1, false);


--
-- TOC entry 4959 (class 0 OID 0)
-- Dependencies: 215
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 13, true);


--
-- TOC entry 4766 (class 2606 OID 26869)
-- Name: airlines_list airlines_list_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.airlines_list
    ADD CONSTRAINT airlines_list_pkey PRIMARY KEY (id);


--
-- TOC entry 4763 (class 2606 OID 26860)
-- Name: airport_list airport_list_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.airport_list
    ADD CONSTRAINT airport_list_pkey PRIMARY KEY (id);


--
-- TOC entry 4768 (class 2606 OID 26905)
-- Name: flight_list flight_list_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.flight_list
    ADD CONSTRAINT flight_list_pkey PRIMARY KEY (id);


--
-- TOC entry 4761 (class 2606 OID 26846)
-- Name: logs logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logs
    ADD CONSTRAINT logs_pkey PRIMARY KEY (id);


--
-- TOC entry 4759 (class 2606 OID 26836)
-- Name: system_settings system_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.system_settings
    ADD CONSTRAINT system_settings_pkey PRIMARY KEY (id);


--
-- TOC entry 4757 (class 2606 OID 26827)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 4770 (class 1259 OID 26957)
-- Name: idx_booked_flight; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_booked_flight ON public.booked_flight USING btree (flight_id);


--
-- TOC entry 4764 (class 1259 OID 26958)
-- Name: idx_flight_airport; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_flight_airport ON public.airport_list USING btree (location);


--
-- TOC entry 4769 (class 1259 OID 26959)
-- Name: idx_flight_list; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_flight_list ON public.flight_list USING btree (airline_id, departure_airport_id, arrival_airport_id);


--
-- TOC entry 4777 (class 2620 OID 26979)
-- Name: users after_user_insert; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER after_user_insert AFTER INSERT ON public.users FOR EACH ROW EXECUTE FUNCTION public.after_user_insert_func();


--
-- TOC entry 4778 (class 2620 OID 26980)
-- Name: users after_user_update; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER after_user_update AFTER UPDATE ON public.users FOR EACH ROW EXECUTE FUNCTION public.after_user_update_func();


--
-- TOC entry 4779 (class 2620 OID 26981)
-- Name: users before_user_delete; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER before_user_delete BEFORE DELETE ON public.users FOR EACH ROW EXECUTE FUNCTION public.before_user_delete_func();


--
-- TOC entry 4775 (class 2606 OID 26987)
-- Name: booked_flight booked_flight_flight_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.booked_flight
    ADD CONSTRAINT booked_flight_flight_id_fkey FOREIGN KEY (flight_id) REFERENCES public.flight_list(id) ON DELETE CASCADE;


--
-- TOC entry 4776 (class 2606 OID 26928)
-- Name: booked_flight booked_flight_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.booked_flight
    ADD CONSTRAINT booked_flight_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- TOC entry 4772 (class 2606 OID 26906)
-- Name: flight_list flight_list_airline_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.flight_list
    ADD CONSTRAINT flight_list_airline_id_fkey FOREIGN KEY (airline_id) REFERENCES public.airlines_list(id);


--
-- TOC entry 4773 (class 2606 OID 26916)
-- Name: flight_list flight_list_arrival_airport_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.flight_list
    ADD CONSTRAINT flight_list_arrival_airport_id_fkey FOREIGN KEY (arrival_airport_id) REFERENCES public.airport_list(id);


--
-- TOC entry 4774 (class 2606 OID 26911)
-- Name: flight_list flight_list_departure_airport_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.flight_list
    ADD CONSTRAINT flight_list_departure_airport_id_fkey FOREIGN KEY (departure_airport_id) REFERENCES public.airport_list(id);


--
-- TOC entry 4771 (class 2606 OID 26971)
-- Name: logs logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logs
    ADD CONSTRAINT logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4939 (class 0 OID 26940)
-- Dependencies: 228 4943
-- Name: airline_booking_summary; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: postgres
--

REFRESH MATERIALIZED VIEW public.airline_booking_summary;


-- Completed on 2024-12-23 04:51:25

--
-- PostgreSQL database dump complete
--

