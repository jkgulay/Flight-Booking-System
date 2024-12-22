--
-- PostgreSQL database dump
--

-- Dumped from database version 16.1
-- Dumped by pg_dump version 16.1

-- Started on 2024-12-23 01:18:22

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

DROP DATABASE IF EXISTS flight_booking_db;
--
-- TOC entry 4927 (class 1262 OID 26540)
-- Name: flight_booking_db; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE flight_booking_db WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'English_Philippines.1252';


ALTER DATABASE flight_booking_db OWNER TO postgres;

\connect flight_booking_db

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
-- TOC entry 230 (class 1255 OID 26719)
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
-- TOC entry 231 (class 1255 OID 26721)
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
-- TOC entry 232 (class 1255 OID 26723)
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
-- TOC entry 233 (class 1255 OID 26737)
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
-- TOC entry 229 (class 1255 OID 26718)
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
-- TOC entry 227 (class 1255 OID 26716)
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
-- TOC entry 228 (class 1255 OID 26717)
-- Name: refresh_airline_booking_summary(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.refresh_airline_booking_summary() RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
    TRUNCATE TABLE airline_booking_summary_materialized;

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
-- TOC entry 218 (class 1259 OID 26644)
-- Name: airlines_list; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.airlines_list (
    id BIGSERIAL PRIMARY KEY,
    airlines TEXT NOT NULL,
    logo_path TEXT NOT NULL
);


ALTER TABLE public.airlines_list OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 26673)
-- Name: booked_flight; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.booked_flight (
    user_id bigint NOT NULL,
    flight_id bigint NOT NULL,
    name text NOT NULL,
    address text NOT NULL,
    contact text NOT NULL,
    status character varying(10) DEFAULT 'pending'::character varying,
    id bigint NOT NULL,
    CONSTRAINT booked_flight_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'accepted'::character varying, 'decline'::character varying])::text[])))
);


ALTER TABLE public.booked_flight OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 26651)
-- Name: flight_list; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.flight_list (
    id BIGSERIAL PRIMARY KEY,
    airline_id BIGINT NOT NULL,
    plane_no CHARACTER VARYING(50) NOT NULL,
    departure_airport_id BIGINT NOT NULL,
    arrival_airport_id BIGINT NOT NULL,
    departure_datetime TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    arrival_datetime TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    seats INTEGER DEFAULT 0 NOT NULL,
    price NUMERIC(10,2) NOT NULL,
    date_created TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.flight_list OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 26794)
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
-- TOC entry 217 (class 1259 OID 26637)
-- Name: airport_list; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.airport_list (
    id BIGSERIAL PRIMARY KEY,
    airport TEXT NOT NULL,
    location TEXT NOT NULL
);



ALTER TABLE public.airport_list OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 26784)
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
-- TOC entry 4928 (class 0 OID 0)
-- Dependencies: 224
-- Name: booked_flight_new_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.booked_flight_new_id_seq OWNED BY public.booked_flight.id;


--
-- TOC entry 226 (class 1259 OID 26801)
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
-- TOC entry 221 (class 1259 OID 26705)
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
-- TOC entry 223 (class 1259 OID 26770)
-- Name: logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    action_type CHARACTER VARYING(50),
    "timestamp" TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    table_name CHARACTER VARYING(50),
    affected_columns TEXT,
    details TEXT
);

ALTER TABLE public.logs OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 26769)
-- Name: logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.logs_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.logs_id_seq OWNER TO postgres;

--
-- TOC entry 4929 (class 0 OID 0)
-- Dependencies: 222
-- Name: logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.logs_id_seq OWNED BY public.logs.id;


--
-- TOC entry 216 (class 1259 OID 26600)
-- Name: system_settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.system_settings (
    id BIGSERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    email CHARACTER VARYING(200) NOT NULL,
    contact CHARACTER VARYING(20) NOT NULL,
    cover_img TEXT NOT NULL,
    about_content TEXT NOT NULL
);


ALTER TABLE public.system_settings OWNER TO postgres;

--
-- TOC entry 215 (class 1259 OID 26591)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id BIGSERIAL PRIMARY KEY,
    name CHARACTER VARYING(200) NOT NULL,
    address TEXT NOT NULL,
    contact TEXT NOT NULL,
    username CHARACTER VARYING(100) NOT NULL,
    password CHARACTER VARYING(200) NOT NULL,
    type SMALLINT DEFAULT 2 NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT users_type_check CHECK ((type = ANY (ARRAY[1, 2, 3])))
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 4737 (class 2604 OID 26785)
-- Name: booked_flight id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.booked_flight ALTER COLUMN id SET DEFAULT nextval('public.booked_flight_new_id_seq'::regclass);


--
-- TOC entry 4738 (class 2604 OID 26773)
-- Name: logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logs ALTER COLUMN id SET DEFAULT nextval('public.logs_id_seq'::regclass);


--
-- TOC entry 4915 (class 0 OID 26644)
-- Dependencies: 218
-- Data for Name: airlines_list; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.airlines_list VALUES (1, 'AirAsia', '1600999080_kisspng-flight-indonesia-airasia-airasia-japan-airline-tic-asia-5abad146966736.8321896415221927106161.jpg') ON CONFLICT DO NOTHING;
INSERT INTO public.airlines_list VALUES (2, 'Philippine Airlines', '1600999200_Philippine-Airlines-Logo.jpg') ON CONFLICT DO NOTHING;
INSERT INTO public.airlines_list VALUES (3, 'Cebu Pacific', '1600999200_43cada0008538e3c1a1f4675e5a7aabe.jpeg') ON CONFLICT DO NOTHING;
INSERT INTO public.airlines_list VALUES (5, 'Cebu Mactan', '1734635640_5-removebg-preview.png') ON CONFLICT DO NOTHING;
INSERT INTO public.airlines_list VALUES (14, 'Bachelor Express', '1734637680_1.png') ON CONFLICT DO NOTHING;


--
-- TOC entry 4914 (class 0 OID 26637)
-- Dependencies: 217
-- Data for Name: airport_list; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.airport_list VALUES (1, 'NAIA', 'Metro Manila') ON CONFLICT DO NOTHING;
INSERT INTO public.airport_list VALUES (2, 'Beijing Capital International Airport', 'Chaoyang-Shunyi, Beijing') ON CONFLICT DO NOTHING;
INSERT INTO public.airport_list VALUES (3, 'Los Angeles International Airport', 'Los Angeles, California') ON CONFLICT DO NOTHING;
INSERT INTO public.airport_list VALUES (4, 'Dubai International Airport', 'Garhoud, Dubai') ON CONFLICT DO NOTHING;
INSERT INTO public.airport_list VALUES (5, 'Mactan-Cebu Airport', 'Cebu') ON CONFLICT DO NOTHING;
INSERT INTO public.airport_list VALUES (6, 'Bancasi Airport', 'Butuan City') ON CONFLICT DO NOTHING;


--
-- TOC entry 4917 (class 0 OID 26673)
-- Dependencies: 220
-- Data for Name: booked_flight; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.booked_flight VALUES (18, 3, 'Admin', 'p-5,poblacion 7, buenavista adn.', '09100290521', 'accepted', 1) ON CONFLICT DO NOTHING;
INSERT INTO public.booked_flight VALUES (18, 24, 'Admin', 'p-5,poblacion 7, buenavista adn.', '09100290521', 'pending', 2) ON CONFLICT DO NOTHING;


--
-- TOC entry 4916 (class 0 OID 26651)
-- Dependencies: 219
-- Data for Name: flight_list; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.flight_list VALUES (3, 3, 'CEB-1101', 5, 1, '2020-09-30 08:00:00', '2020-09-30 08:45:00', 100, 2500.00, '2020-09-25 11:57:31') ON CONFLICT DO NOTHING;
INSERT INTO public.flight_list VALUES (18, 2, '2', 2, 6, '2024-12-20 08:25:00', '2024-12-21 08:25:00', 1, 2.00, '2024-12-20 00:25:29') ON CONFLICT DO NOTHING;
INSERT INTO public.flight_list VALUES (22, 1, 'w2', 1, 6, '2024-12-27 00:04:00', '2024-12-27 11:03:00', 1, 1.00, '2024-12-20 01:01:32') ON CONFLICT DO NOTHING;
INSERT INTO public.flight_list VALUES (24, 14, '221', 3, 6, '2024-12-21 09:08:00', '2024-12-22 09:08:00', 1, 1.00, '2024-12-20 01:08:48') ON CONFLICT DO NOTHING;


--
-- TOC entry 4919 (class 0 OID 26770)
-- Dependencies: 223
-- Data for Name: logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.logs VALUES (1, 10, 'UPDATE', '2024-12-23 01:01:37.895967', 'users', 'Updated columns', 'User  details updated') ON CONFLICT DO NOTHING;


--
-- TOC entry 4913 (class 0 OID 26600)
-- Dependencies: 216
-- Data for Name: system_settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.system_settings VALUES (1, 'Online Flight Booking System', 'info@sample.com', '+6948 8542 623', '1600998360_travel-cover.jpg', 'Lorem Ipsum description.') ON CONFLICT DO NOTHING;


--
-- TOC entry 4912 (class 0 OID 26591)
-- Dependencies: 215
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.users VALUES (9, 2, 'DR.James Smith, M.D.', 'Sample Clinic Address', '+1456 554 55623', 'jsmith@sample.com', 'jsmith123', 3, '2024-12-23 00:32:12.599778') ON CONFLICT DO NOTHING;
INSERT INTO public.users VALUES (15, 9, 'DR.Sample Doctor, M.D.', 'Buenavista', '+ 1235 456 623', 'sample2@sample.com', 'sample123', 2, '2024-12-23 00:32:12.599778') ON CONFLICT DO NOTHING;
INSERT INTO public.users VALUES (16, 0, 'Jun Kyle Gulay', 'Butuan City', '09100290521', 'junkyle.gulay', '$2y$10$0v1WXe.ILfW7n8IxKwg97.gVai/1htOiWCdZe3029lmp9CAk14zSO', 3, '2024-12-23 00:32:12.599778') ON CONFLICT DO NOTHING;
INSERT INTO public.users VALUES (17, 0, 'Gummy Worms', 'p-5,poblacion 7, buenavista adn.', '09876543211', 'katzukii21', '$2y$10$soSSUffvO3pYKD.q0Rjp9eTJ6UGMSqiYAH2lfcst2loUEbWbdNl7a', 3, '2024-12-23 00:32:12.599778') ON CONFLICT DO NOTHING;
INSERT INTO public.users VALUES (18, 0, 'Admin', 'Butuan City', '09100290521', 'admin', '$2y$10$EeMbofo.QX1UZSaRQIlvd.rESdVKoSNOIZ9GDVL1DA52aUBoc2q2i', 1, '2024-12-23 00:32:12.599778') ON CONFLICT DO NOTHING;
INSERT INTO public.users VALUES (25, 0, 'Aldwin', 'Test Address', '1234567890', 'testuser', 'password123', 3, '2024-12-23 00:32:12.599778') ON CONFLICT DO NOTHING;
INSERT INTO public.users VALUES (40, 0, 'Jun Kyle Gulay', 'Butuan City', '09100290521', 'kayel2002', '$2y$10$GIEhpWbRJ7OR8Js/TefNMuOTXdE9WIWBQNF/5Oz5naYEhvSUhSzMK', 2, '2024-12-23 00:32:12.599778') ON CONFLICT DO NOTHING;
INSERT INTO public.users VALUES (10, 3, 'DR.Claire Blake, M.D.', 'Sample Only', '+5465 555 623', 'cblake@sample.com', 'blake123', 3, '2024-12-23 00:32:12.599778') ON CONFLICT DO NOTHING;


--
-- TOC entry 4930 (class 0 OID 0)
-- Dependencies: 224
-- Name: booked_flight_new_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.booked_flight_new_id_seq', 2, true);


--
-- TOC entry 4931 (class 0 OID 0)
-- Dependencies: 222
-- Name: logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.logs_id_seq', 1, true);


--
-- TOC entry 4750 (class 2606 OID 26650)
-- Name: airlines_list airlines_list_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.airlines_list
    ADD CONSTRAINT airlines_list_pkey PRIMARY KEY (id);


--
-- TOC entry 4747 (class 2606 OID 26643)
-- Name: airport_list airport_list_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.airport_list
    ADD CONSTRAINT airport_list_pkey PRIMARY KEY (id);


--
-- TOC entry 4752 (class 2606 OID 26657)
-- Name: flight_list flight_list_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.flight_list
    ADD CONSTRAINT flight_list_pkey PRIMARY KEY (id);


--
-- TOC entry 4756 (class 2606 OID 26778)
-- Name: logs logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logs
    ADD CONSTRAINT logs_pkey PRIMARY KEY (id);


--
-- TOC entry 4745 (class 2606 OID 26606)
-- Name: system_settings system_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.system_settings
    ADD CONSTRAINT system_settings_pkey PRIMARY KEY (id);


--
-- TOC entry 4743 (class 2606 OID 26599)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 4754 (class 1259 OID 26711)
-- Name: idx_booked_flight; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_booked_flight ON public.booked_flight USING btree (flight_id);


--
-- TOC entry 4748 (class 1259 OID 26710)
-- Name: idx_flight_airport; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_flight_airport ON public.airport_list USING btree (location);


--
-- TOC entry 4753 (class 1259 OID 26712)
-- Name: idx_flight_list; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_flight_list ON public.flight_list USING btree (airline_id, departure_airport_id, arrival_airport_id);


--
-- TOC entry 4763 (class 2620 OID 26720)
-- Name: users after_user_insert; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER after_user_insert AFTER INSERT ON public.users FOR EACH ROW EXECUTE FUNCTION public.after_user_insert_func();


--
-- TOC entry 4764 (class 2620 OID 26722)
-- Name: users after_user_update; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER after_user_update AFTER UPDATE ON public.users FOR EACH ROW EXECUTE FUNCTION public.after_user_update_func();


--
-- TOC entry 4765 (class 2620 OID 26724)
-- Name: users before_user_delete; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER before_user_delete BEFORE DELETE ON public.users FOR EACH ROW EXECUTE FUNCTION public.before_user_delete_func();


--
-- TOC entry 4760 (class 2606 OID 26682)
-- Name: booked_flight booked_flight_flight_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.booked_flight
    ADD CONSTRAINT booked_flight_flight_id_fkey FOREIGN KEY (flight_id) REFERENCES public.flight_list(id) ON DELETE CASCADE;


--
-- TOC entry 4761 (class 2606 OID 26687)
-- Name: booked_flight booked_flight_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.booked_flight
    ADD CONSTRAINT booked_flight_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 4757 (class 2606 OID 26658)
-- Name: flight_list flight_list_airline_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.flight_list
    ADD CONSTRAINT flight_list_airline_id_fkey FOREIGN KEY (airline_id) REFERENCES public.airlines_list(id) ON DELETE CASCADE;


--
-- TOC entry 4758 (class 2606 OID 26668)
-- Name: flight_list flight_list_arrival_airport_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.flight_list
    ADD CONSTRAINT flight_list_arrival_airport_id_fkey FOREIGN KEY (arrival_airport_id) REFERENCES public.airport_list(id) ON DELETE CASCADE;


--
-- TOC entry 4759 (class 2606 OID 26663)
-- Name: flight_list flight_list_departure_airport_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.flight_list
    ADD CONSTRAINT flight_list_departure_airport_id_fkey FOREIGN KEY (departure_airport_id) REFERENCES public.airport_list(id) ON DELETE CASCADE;


--
-- TOC entry 4762 (class 2606 OID 26779)
-- Name: logs logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.logs
    ADD CONSTRAINT logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- TOC entry 4921 (class 0 OID 26794)
-- Dependencies: 225 4923
-- Name: airline_booking_summary; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: postgres
--

REFRESH MATERIALIZED VIEW public.airline_booking_summary;


-- Completed on 2024-12-23 01:18:22

--
-- PostgreSQL database dump complete
--

