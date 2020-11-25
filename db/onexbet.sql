--
-- PostgreSQL database dump
--

-- Dumped from database version 10.14 (Ubuntu 10.14-0ubuntu0.18.04.1)
-- Dumped by pg_dump version 10.14 (Ubuntu 10.14-0ubuntu0.18.04.1)

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

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: onexbet; Type: TABLE; Schema: public; Owner: datadmin
--

CREATE TABLE public.onexbet (
    id integer NOT NULL,
    matchid character varying(20) NOT NULL,
    sport character varying(200) NOT NULL,
    hometeam character varying(200) NOT NULL,
    awayteam character varying(200) NOT NULL,
    kind integer NOT NULL,
    league character(100) NOT NULL,
    datestring character(50) NOT NULL,
    m_1x2 text,
    handicap_norm text,
    d_chance text,
    hf_time text,
    odd_even text,
    total_1 text,
    total_2 text,
    gn_goal text,
    over_under text,
    h_score_half text,
    h_wins_eith text,
    a_wins_eith text,
    home_oddeven text,
    away_oddeven text,
    asain_hcap text,
    handicap text,
    last_goal text,
    h_clean_sheet text,
    h_highest_score text,
    a_highest_score text,
    h_score_home text,
    a_score_away text,
    h_win_nil text,
    a_win_nil text,
    first_goal_1x2 text,
    exact_goal text,
    a_score_both text,
    h_score_both text,
    dc_ov_goal text,
    c_score_17 text,
    c_score text,
    handicap_half text,
    handicap_2ht text,
    handicap_2half text,
    handicap_1half text,
    ahcap_1half text,
    ahcap_2half text,
    odd_even_ht text,
    even_odd_2half text,
    ou_home_ht text,
    ov_home_2ht text,
    ov_away_ht text,
    ov_away_2ht text,
    dc_first_half text,
    dc_2half text,
    m1x2_ht text,
    m1x2_2ht text,
    correct_score_17_1half text,
    correct_score_17_2half text,
    c_score_half text,
    c_score_2half text,
    exact_goal_2ht text,
    exact_goal_ht text,
    multi_goal text
    
);


ALTER TABLE public.onexbet OWNER TO datadmin;

--
-- Name: onexbet_id_seq; Type: SEQUENCE; Schema: public; Owner: datadmin
--

CREATE SEQUENCE public.onexbet_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.onexbet_id_seq OWNER TO datadmin;

--
-- Name: onexbet_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: datadmin
--

ALTER SEQUENCE public.onexbet_id_seq OWNED BY public.onexbet.id;


--
-- Name: onexbet id; Type: DEFAULT; Schema: public; Owner: datadmin
--

ALTER TABLE ONLY public.onexbet ALTER COLUMN id SET DEFAULT nextval('public.onexbet_id_seq'::regclass);


--
-- Name: onexbet onexbet_pkey; Type: CONSTRAINT; Schema: public; Owner: datadmin
--

ALTER TABLE ONLY public.onexbet
    ADD CONSTRAINT onexbet_pkey PRIMARY KEY (id);


--
-- PostgreSQL database dump complete
--

